<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @property-read Announcements_model  $announcements_model
 * @property-read Authentication_model $Authentication_model
 * @property-read App_Form_validation  $form_validation
 */
class Authentication extends App_Controller
{
    public function __construct()
    {
        parent::__construct();

        if ($this->app->is_db_upgrade_required()) {
            redirect(admin_url());
        }

        load_admin_language();
        $this->load->model('Authentication_model');
        $this->load->library('form_validation');

        $this->form_validation->set_message('required', _l('form_validation_required'));
        $this->form_validation->set_message('valid_email', _l('form_validation_valid_email'));
        $this->form_validation->set_message('matches', _l('form_validation_matches'));

        hooks()->do_action('admin_auth_init');
    }

    public function index()
    {
        $this->admin();
    }

    public function admin()
    {
        if (is_staff_logged_in()) {
            redirect(admin_url());
        }

        $this->form_validation->set_rules('password', _l('admin_auth_login_password'), 'required');
        $this->form_validation->set_rules('email', _l('admin_auth_login_email'), 'trim|required|callback_email_or_empcode');
        if (show_recaptcha()) {
            $this->form_validation->set_rules('g-recaptcha-response', 'Captcha', 'callback_recaptcha');
        }
        if ($this->input->post()) {
            if ($this->form_validation->run() !== false) {
                $email    = $this->input->post('email');
                $password = $this->input->post('password', false);
                $remember = $this->input->post('remember');

                $data = $this->Authentication_model->login($email, $password, $remember, true);

                if (is_array($data) && isset($data['memberinactive'])) {
                    set_alert('danger', _l('admin_auth_inactive_account'));
                    redirect(admin_url('authentication'));
                } elseif (is_array($data) && isset($data['two_factor_auth'])) {
                    $this->session->set_userdata('_two_factor_auth_established', true);
                    if ($data['user']->two_factor_auth_enabled == 1) {
                        $this->Authentication_model->set_two_factor_auth_code($data['user']->staffid);
                        $sent = send_mail_template('staff_two_factor_auth_key', $data['user']);

                        if (! $sent) {
                            set_alert('danger', _l('two_factor_auth_failed_to_send_code'));
                            redirect(admin_url('authentication'));
                        } else {
                            $this->session->set_userdata('_two_factor_auth_staff_email', $email);
                            set_alert('success', _l('two_factor_auth_code_sent_successfully', $email));
                            redirect(admin_url('authentication/two_factor'));
                        }
                    } else {
                        set_alert('success', _l('enter_two_factor_auth_code_from_mobile'));
                        redirect(admin_url('authentication/two_factor/app'));
                    }
                } elseif ($data == false) {
                    set_alert('danger', _l('admin_auth_invalid_email_or_password'));
                    redirect(admin_url('authentication'));
                }

                $this->load->model('announcements_model');
                $this->announcements_model->set_announcements_as_read_except_last_one(get_staff_user_id(), true);

                // is logged in
                maybe_redirect_to_previous_url();

                hooks()->do_action('after_staff_login');
                redirect(admin_url());
            }
        }

        $data['title'] = _l('admin_auth_login_heading');
        $this->load->view('authentication/login_admin', $data);
    }

    public function two_factor($type = 'email')
    {
        if (! $this->session->has_userdata('_two_factor_auth_established')) {
            show_404();
        }

        $this->form_validation->set_rules('code', _l('two_factor_authentication_code'), 'required');

        if ($this->input->post()) {
            if ($this->form_validation->run() !== false) {
                $code  = $this->input->post('code');
                $code  = trim($code);
                $email = $this->session->userdata('_two_factor_auth_staff_email');
                if ($this->Authentication_model->is_two_factor_code_valid($code, $email) && $type = 'email') {
                    $this->session->unset_userdata('_two_factor_auth_staff_email');

                    $user = $this->Authentication_model->get_user_by_two_factor_auth_code($code);
                    $this->Authentication_model->clear_two_factor_auth_code($user->staffid);
                    $this->Authentication_model->two_factor_auth_login($user);
                    $this->session->unset_userdata('_two_factor_auth_established');
                    $this->load->model('announcements_model');
                    $this->announcements_model->set_announcements_as_read_except_last_one(get_staff_user_id(), true);

                    maybe_redirect_to_previous_url();

                    hooks()->do_action('after_staff_login');
                    redirect(admin_url());
                } elseif ($this->Authentication_model->is_google_two_factor_code_valid($code) && $type = 'app') {
                    $user = get_staff($this->session->userdata('tfa_staffid'));
                    $this->Authentication_model->two_factor_auth_login($user);
                    $this->session->unset_userdata('_two_factor_auth_established');
                    $this->load->model('announcements_model');
                    $this->announcements_model->set_announcements_as_read_except_last_one(get_staff_user_id(), true);

                    maybe_redirect_to_previous_url();

                    hooks()->do_action('after_staff_login');
                    redirect(admin_url());
                } else {
                    log_activity('Failed Two factor authentication attempt [Staff Name: ' . get_staff_full_name() . ', IP: ' . $this->input->ip_address() . ']');

                    set_alert('danger', _l('two_factor_code_not_valid'));
                    redirect(admin_url('authentication/two_factor/' . $type));
                }
            }
        }

        $this->load->view('authentication/set_two_factor_auth_code');
    }

    public function forgot_password()
    {
        if (is_staff_logged_in()) {
            redirect(admin_url());
        }

        // Check if we're on the OTP method selection step
        if ($this->session->has_userdata('otp_options_staffid')) {
            return $this->_handle_otp_method_selection();
        }

        $this->form_validation->set_rules('emp_code', 'Employee Code', 'trim|required|callback_emp_code_exists');
        if ($this->input->post()) {
            if ($this->form_validation->run() !== false) {
                $result = $this->Authentication_model->forgot_password_by_empcode($this->input->post('emp_code'));
                if (is_array($result) && isset($result['memberinactive'])) {
                    set_alert('danger', _l('inactive_account'));
                    redirect(admin_url('authentication/forgot_password'));
                } elseif (isset($result['user'])) {
                    $user = $result['user'];

                    // Check available communication methods
                    $has_phone = !empty($user->phonenumber);
                    $has_email = !empty($user->email);

                    if (!$has_phone && !$has_email) {
                        set_alert('danger', 'No communication options available. Please contact IT team.');
                        redirect(admin_url('authentication/forgot_password'));
                    }

                    // Store user info for next step
                    $this->session->set_userdata('otp_options_staffid', $user->staffid);

                    // Always show choice page before sending OTP
                    return $this->_show_otp_method_selection($user->staffid, $has_phone, $has_email);
                } else {
                    set_alert('danger', 'Failed to send OTP. Please try again.');
                    redirect(admin_url('authentication/forgot_password'));
                }
            }
        }
        $data['title'] = 'Forgot Password - Enter Employee Code';
        $this->load->view('authentication/forgot_password', $data);
    }

    private function _show_otp_method_selection($staffid, $has_phone, $has_email)
    {
        $data['title'] = 'Choose OTP Method';
        $data['staffid'] = $staffid;
        $data['has_phone'] = $has_phone;
        $data['has_email'] = $has_email;

        // Get phone number for masking if available
        if ($has_phone) {
            $phone_result = $this->db->select('phonenumber')->where('staffid', $staffid)->get(db_prefix() . 'staff')->row();
            $phone = $phone_result ? $phone_result->phonenumber : '';
            if ($phone) {
                $mask_len = max(0, strlen($phone) - 4);
                $last_len = min(4, strlen($phone));
                $data['masked_phone'] = str_repeat('x', $mask_len) . substr($phone, - $last_len);
            }
        }

        // Get email for masking if available
        if ($has_email) {
            $user = $this->db->select('email')->where('staffid', $staffid)->get(db_prefix() . 'staff')->row();
            if ($user && $user->email) {
                $data['masked_email'] = $this->_mask_email($user->email);
            }
        }

        $this->load->view('authentication/choose_otp_method', $data);
    }

    private function _handle_otp_method_selection()
    {
        $staffid = $this->session->userdata('otp_options_staffid');

        if (!$staffid) {
            redirect(admin_url('authentication/forgot_password'));
        }

        if ($this->input->post('otp_method')) {
            $method = $this->input->post('otp_method');
            return $this->_send_otp_via_method($method, $staffid);
        }

        // Re-show selection if no method chosen
        $user = $this->db->where('staffid', $staffid)->get(db_prefix() . 'staff')->row();
        if ($user) {
            $has_phone = !empty($user->phonenumber);
            $has_email = !empty($user->email);
            return $this->_show_otp_method_selection($staffid, $has_phone, $has_email);
        }

        redirect(admin_url('authentication/forgot_password'));
    }

    private function _send_otp_via_method($method, $staffid)
    {
        // Generate OTP and send based on method
        $otp = rand(10000, 99999);
        $this->session->set_userdata('password_reset_otp_' . $staffid, $otp);
        $this->session->set_userdata('password_reset_otp_time_' . $staffid, time());
        $this->session->set_userdata('password_reset_otp_email_' . $staffid, ''); // Will be set based on method
        $this->session->set_userdata('password_reset_otp_method_' . $staffid, $method); // Store the chosen method

        $this->session->set_userdata('password_reset_otp_staffid', $staffid);
        $this->session->unset_userdata('otp_options_staffid');

        if ($method === 'email') {
            // Send email OTP
            $user = $this->db->where('staffid', $staffid)->get(db_prefix() . 'staff')->row();
            $this->session->set_userdata('password_reset_otp_email_' . $staffid, $user->email);

            if ($this->_send_otp_by_email($user->email, $otp)) {
                $masked_email = $this->_mask_email($user->email);
                set_alert('success', 'OTP sent to your registered email address ' . $masked_email . '.');
            } else {
                set_alert('danger', 'Failed to send OTP email. Please try again.');
                redirect(admin_url('authentication/forgot_password'));
            }
        } else {
            // Send SMS OTP (existing functionality)
            $user = $this->db->where('staffid', $staffid)->get(db_prefix() . 'staff')->row();
            $this->session->set_userdata('password_reset_otp_email_' . $staffid, $user->email);

            if (!empty($user->phonenumber)) {
                $this->load->library('sms/App_sms');
                $this->app_sms->trigger(SMS_TRIGGER_STAFF_PASSWORD_RESET, $user->phonenumber, ['otp_code' => $otp, 'staff_id' => $staffid]);

                // Get masked phone
                $phone = $user->phonenumber;
                $mask_len = max(0, strlen($phone) - 4);
                $last_len = min(4, strlen($phone));
                $masked_phone = str_repeat('x', $mask_len) . substr($phone, - $last_len);
                set_alert('success', 'OTP sent to your registered mobile number ' . $masked_phone . '.');
            } else {
                set_alert('danger', 'Phone number not available. Please try again.');
                redirect(admin_url('authentication/forgot_password'));
            }
        }

        redirect(admin_url('authentication/verify_otp'));
    }

    private function _mask_email($email)
    {
        list($local, $domain) = explode('@', $email);
        $len = strlen($local);
        if ($len <= 3) {
            $masked_local = $local[0] . str_repeat('x', $len - 1);
        } else {
            $masked_local = substr($local, 0, 3) . str_repeat('x', $len - 3);
        }
        return $masked_local . '@' . $domain;
    }

    private function _send_otp_by_email($email, $otp)
    {
        // Get staff user by email
        $this->db->where('email', $email);
        $user = $this->db->get(db_prefix() . 'staff')->row();

        if ($user) {
            // Send OTP via email using existing staff_forgot_password template
            $template_data = [
                'new_pass_key' => $otp, // Using OTP as new_pass_key for display
                'otp_code' => $otp, // OTP code for potential use
                'staff' => 1,
                'userid' => $user->staffid,
            ];

            return send_mail_template('staff_forgot_password', $user->email, $user->staffid, $template_data);
        }

        return false;
    }

    public function forgot_password_otp()
    {
        if (is_staff_logged_in()) {
            redirect(admin_url());
        }
        $this->form_validation->set_rules('emp_code', 'Employee Code', 'trim|required|callback_emp_code_exists');
        if ($this->input->post()) {
            if ($this->form_validation->run() !== false) {
                $result = $this->Authentication_model->forgot_password_by_empcode($this->input->post('emp_code'));
                if (is_array($result) && isset($result['memberinactive'])) {
                    set_alert('danger', _l('inactive_account'));
                    redirect(admin_url('authentication/forgot_password_otp'));
                } elseif (isset($result['user'])) {
                    // Store user info in session for next step
                    $this->session->set_userdata('password_reset_otp_staffid', $result['user']->staffid);
                    // Get phone number for masking
                    $phone_result = $this->db->select('phonenumber')->where('staffid', $result['user']->staffid)->get(db_prefix() . 'staff')->row();
                    $phone = $phone_result ? $phone_result->phonenumber : '';
                    if ($phone) {
                        $mask_len = max(0, strlen($phone) - 4);
                        $last_len = min(4, strlen($phone));
                        $masked_phone = str_repeat('x', $mask_len) . substr($phone, - $last_len);
                    } else {
                        $masked_phone = '';
                    }
                    set_alert('success', 'OTP sent to your registered mobile number ' . $masked_phone . '.');
                    redirect(admin_url('authentication/verify_otp'));
                } else {
                    set_alert('danger', 'Failed to send OTP. Please try again.');
                    redirect(admin_url('authentication/forgot_password_otp'));
                }
            }
        }
        $data['title'] = 'Forgot Password - Enter Employee Code';
        $this->load->view('authentication/forgot_password_otp', $data);
    }

    public function verify_otp()
    {
        if (is_staff_logged_in() || !$this->session->has_userdata('password_reset_otp_staffid')) {
            redirect(admin_url());
        }
        $this->form_validation->set_rules('otp[]', 'OTP Code', 'trim|required|numeric|exact_length[1]');
        if ($this->input->post()) {
            if ($this->form_validation->run() !== false) {
                $staffid = $this->session->userdata('password_reset_otp_staffid');
                $otp_array = $this->input->post('otp');
                if (!is_array($otp_array) || count($otp_array) !== 5 || !preg_match('/^[0-9]{5}$/', implode('', $otp_array))) {
                    set_alert('danger', 'Invalid OTP format. OTP must be 5 digits.');
                    redirect(admin_url('authentication/verify_otp'));
                }
                $otp = implode('', $otp_array);

                if ($this->Authentication_model->verify_otp_code($staffid, $otp)) {
                    // Generate reset key and redirect to password setup
                    $reset_key = md5(uniqid(mt_rand(), true));
                    $update_data = [
                        'new_pass_key' => $reset_key,
                        'new_pass_key_requested' => date('Y-m-d H:i:s'),
                    ];

                    $this->db->where('staffid', $staffid);
                    $result = $this->db->update(db_prefix() . 'staff', $update_data);

                    if ($result) {
                        $this->session->unset_userdata('password_reset_otp_staffid');
                        redirect(admin_url('authentication/reset_password_otp/' . $staffid . '/' . $reset_key));
                    } else {
                        // Database update failed
                        set_alert('danger', 'Failed to generate reset key. Please try again.');
                        redirect(admin_url('authentication/verify_otp'));
                    }
                } else {
                    set_alert('danger', 'Invalid OTP code. Please try again.');
                }
            }
        }

        // Get OTP expiry information for countdown timer
        $staffid = $this->session->userdata('password_reset_otp_staffid');
        $otp_time = $this->session->userdata('password_reset_otp_time_' . $staffid);
        $otp_expiry_minutes = get_option('otp_expiry_minutes', 10);

        $data['title'] = 'Verify OTP Code';
        $data['otp_expiry_minutes'] = $otp_expiry_minutes;
        $data['otp_time'] = $otp_time;
        $data['otp_resend_cooldown'] = get_option('otp_resend_cooldown') ?: 60;

        $this->load->view('authentication/verify_otp', $data);
    }

    public function resend_otp()
    {
        if (is_staff_logged_in() || !$this->session->has_userdata('password_reset_otp_staffid')) {
            redirect(admin_url());
        }

        $staffid = $this->session->userdata('password_reset_otp_staffid');

        // Get the original method used for OTP
        $original_method = $this->session->userdata('password_reset_otp_method_' . $staffid);

        if (!$original_method) {
            // If no method stored, default to SMS (backward compatibility)
            $original_method = 'sms';
        }

        // Generate new OTP and send via the original method
        $otp = rand(10000, 99999);
        $this->session->set_userdata('password_reset_otp_' . $staffid, $otp);
        $this->session->set_userdata('password_reset_otp_time_' . $staffid, time());

        if ($original_method === 'email') {
            // Resend via email
            $user = $this->db->where('staffid', $staffid)->get(db_prefix() . 'staff')->row();

            if ($this->_send_otp_by_email($user->email, $otp)) {
                $masked_email = $this->_mask_email($user->email);
                set_alert('success', 'OTP has been resent to your registered email address ' . $masked_email . '.');
            } else {
                set_alert('danger', 'Failed to resend OTP email. Please try again.');
                redirect(admin_url('authentication/verify_otp'));
            }
        } else {
            // Resend via SMS
            $user = $this->db->where('staffid', $staffid)->get(db_prefix() . 'staff')->row();

            if (!empty($user->phonenumber)) {
                $this->load->library('sms/App_sms');
                $this->app_sms->trigger(SMS_TRIGGER_STAFF_PASSWORD_RESET, $user->phonenumber, ['otp_code' => $otp, 'staff_id' => $staffid]);

                // Get masked phone
                $phone = $user->phonenumber;
                $mask_len = max(0, strlen($phone) - 4);
                $last_len = min(4, strlen($phone));
                $masked_phone = str_repeat('x', $mask_len) . substr($phone, - $last_len);
                set_alert('success', 'OTP has been resent to your registered mobile number ' . $masked_phone . '.');
            } else {
                set_alert('danger', 'Phone number not available. Please try again.');
                redirect(admin_url('authentication/verify_otp'));
            }
        }

        redirect(admin_url('authentication/verify_otp'));
    }

    public function reset_password_otp($staffid, $reset_key)
    {
        if (!$this->Authentication_model->can_reset_password(true, $staffid, $reset_key)) {
            set_alert('danger', _l('password_reset_key_expired'));
            redirect(admin_url('authentication/forgot_password_otp'));
        }
        $this->form_validation->set_rules('password', _l('admin_auth_reset_password'), 'required|callback_password_strength_check');
        $this->form_validation->set_rules('passwordr', _l('admin_auth_reset_password_repeat'), 'required|matches[password]');
        if ($this->input->post()) {
            if ($this->form_validation->run() !== false) {
                hooks()->do_action('before_user_reset_password', [
                    'staff'  => true,
                    'userid' => $staffid,
                ]);
                $success = $this->Authentication_model->reset_password(true, $staffid, $reset_key, $this->input->post('passwordr', false));
                if (is_array($success) && $success['expired'] == true) {
                    set_alert('danger', _l('password_reset_key_expired'));
                } elseif ($success == true) {
                    hooks()->do_action('after_user_reset_password', [
                        'staff'  => true,
                        'userid' => $staffid,
                    ]);
                    set_alert('success', _l('password_reset_message'));

                    // Update welcome popup flag in database (set to 1 to indicate popup has been shown)
                    $this->db->where('staffid', $staffid);
                    $this->db->update(db_prefix() . 'staff', ['welcome_popup_shown' => 1]);
                    log_message('debug', 'Welcome popup flag updated to 1 for user ID: ' . $staffid);
                } else {
                    set_alert('danger', _l('password_reset_message_fail'));
                }
                redirect(admin_url('authentication'));
            }
        }
        $data['title'] = 'Set New Password';
        $this->load->view('authentication/reset_password_otp', $data);
    }

    public function reset_password($staff, $userid, $new_pass_key)
    {
        if (! $this->Authentication_model->can_reset_password($staff, $userid, $new_pass_key)) {
            set_alert('danger', _l('password_reset_key_expired'));
            redirect(admin_url('authentication'));
        }
        $this->form_validation->set_rules('password', _l('admin_auth_reset_password'), 'required');
        $this->form_validation->set_rules('passwordr', _l('admin_auth_reset_password_repeat'), 'required|matches[password]');
        if ($this->input->post()) {
            if ($this->form_validation->run() !== false) {
                hooks()->do_action('before_user_reset_password', [
                    'staff'  => $staff,
                    'userid' => $userid,
                ]);
                $success = $this->Authentication_model->reset_password($staff, $userid, $new_pass_key, $this->input->post('passwordr', false));
                if (is_array($success) && $success['expired'] == true) {
                    set_alert('danger', _l('password_reset_key_expired'));
                } elseif ($success == true) {
                    hooks()->do_action('after_user_reset_password', [
                        'staff'  => $staff,
                        'userid' => $userid,
                    ]);
                    set_alert('success', _l('password_reset_message'));

                    // Update welcome popup flag in database (set to 1 to indicate popup has been shown)
                    $this->db->where('staffid', $userid);
                    $this->db->update(db_prefix() . 'staff', ['welcome_popup_shown' => 1]);
                    log_message('debug', 'Welcome popup flag updated to 1 for user ID: ' . $userid);
                } else {
                    set_alert('danger', _l('password_reset_message_fail'));
                }
                redirect(admin_url('authentication'));
            }
        }
        $this->load->view('authentication/reset_password');
    }

    public function set_password($staff, $userid, $new_pass_key)
    {
        if (! $this->Authentication_model->can_set_password($staff, $userid, $new_pass_key)) {
            set_alert('danger', _l('password_reset_key_expired'));
            if ($staff == 1) {
                redirect(admin_url('authentication'));
            } else {
                redirect(site_url('authentication'));
            }
        }
        $this->form_validation->set_rules('password', _l('admin_auth_set_password'), 'required');
        $this->form_validation->set_rules('passwordr', _l('admin_auth_set_password_repeat'), 'required|matches[password]');
        if ($this->input->post()) {
            if ($this->form_validation->run() !== false) {
                $success = $this->Authentication_model->set_password($staff, $userid, $new_pass_key, $this->input->post('passwordr', false));
                if (is_array($success) && $success['expired'] == true) {
                    set_alert('danger', _l('password_reset_key_expired'));
                } elseif ($success == true) {
                    set_alert('success', _l('password_reset_message'));

                    // Update welcome popup flag in database (set to 1 to indicate popup has been shown)
                    if ($staff == 1) {
                        $this->db->where('staffid', $userid);
                        $this->db->update(db_prefix() . 'staff', ['welcome_popup_shown' => 1]);
                        log_message('debug', 'Welcome popup flag updated to 1 for user ID: ' . $userid);
                    }
                } else {
                    set_alert('danger', _l('password_reset_message_fail'));
                }
                if ($staff == 1) {
                    redirect(admin_url('authentication'));
                } else {
                    redirect(site_url());
                }
            }
        }
        $this->load->view('authentication/set_password');
    }

    public function logout()
    {
        $this->Authentication_model->logout();
        hooks()->do_action('after_user_logout');
        redirect(admin_url('authentication'));
    }

    public function email_exists($email)
    {
        $total_rows = total_rows(db_prefix() . 'staff', [
            'email' => $email,
        ]);
        if ($total_rows == 0) {
            $this->form_validation->set_message('email_exists', _l('auth_reset_pass_email_not_found'));

            return false;
        }

        return true;
    }

    public function emp_code_exists($emp_code)
    {
        // Check if employee code exists in custom fields
        $this->db->select('cfv.value');
        $this->db->from(db_prefix().'customfieldsvalues cfv');
        $this->db->join(db_prefix().'customfields cf', 'cf.id = cfv.fieldid', 'left');
        $this->db->join(db_prefix().'staff s', 's.staffid = cfv.relid', 'left');
        $this->db->where('cf.slug', 'staff_emp_code');
        $this->db->where('cfv.fieldto', 'staff');
        $this->db->where('cfv.value', $emp_code);
        $this->db->where('s.active', 1);
        $total_rows = $this->db->count_all_results();

        if ($total_rows == 0) {
            $this->form_validation->set_message('emp_code_exists', 'Employee code not found or inactive user.');

            return false;
        }

        return true;
    }

    /**
     * Callback function to validate email or employee code
     */
    public function email_or_empcode($str)
    {
        try {
            // First check if it's a valid email format (if it contains @)
            if (strpos($str, '@') !== false) {
                // It's an email, validate it as email
                if (!filter_var($str, FILTER_VALIDATE_EMAIL)) {
                    $this->form_validation->set_message('email_or_empcode', 'Please enter a valid email address.');
                    return false;
                }

                // Check if email exists in staff table for active staff
                $query = $this->db->where('email', $str)->where('active', 1)->get(db_prefix() . 'staff');
                if ($query->num_rows() == 0) {
                    $this->form_validation->set_message('email_or_empcode', _l('admin_auth_invalid_email_or_password'));
                    return false;
                }
            } else {
                // For now, allow non-email inputs to pass validation
                // The actual authentication will be handled in the model
                if (strlen($str) < 2) {
                    $this->form_validation->set_message('email_or_empcode', 'Please enter at least 2 characters.');
                    return false;
                }
            }

            return true;
        } catch (Exception $e) {
            // If there's any error, log it and allow to pass to model for proper handling
            error_log('Email validation error: ' . $e->getMessage());
            return true;
        }
    }

    public function recaptcha($str = '')
    {
        return do_recaptcha_validation($str);
    }

    public function get_qr()
    {
        if (! is_staff_logged_in()) {
            ajax_access_denied();
        }

        $company_name = preg_replace('/:/', '-', get_option('companyname'));

        if ($company_name == '') {
            // Colons is not allowed in the issuer name
            $company_name = rtrim(preg_replace('/^https?:\/\//', '', site_url()), '/') . ' - CRM';
        }

        $data = $this->Authentication_model->get_qr($company_name);
        $this->load->view('admin/includes/google_two_factor', $data);
    }

    /**
     * Dismiss welcome popup - update database flag
     */
    public function dismiss_welcome_popup()
    {
        if (! is_staff_logged_in()) {
            ajax_access_denied();
        }

        // Update welcome popup flag in database (set to 1 to indicate popup has been shown)
        $this->db->where('staffid', get_staff_user_id());
        $this->db->update(db_prefix() . 'staff', ['welcome_popup_shown' => 1]);

        echo json_encode(['success' => true]);
    }

    /**
     * Callback function to validate password strength
     */
    public function password_strength_check($password)
    {
        if (empty($password)) {
            $this->form_validation->set_message('password_strength_check', 'Password is required.');
            return false;
        }

        // Check minimum length (8 characters)
        if (strlen($password) < 8) {
            $this->form_validation->set_message('password_strength_check', 'Password must be at least 8 characters long.');
            return false;
        }

        // Check for at least one uppercase letter
        if (!preg_match('/[A-Z]/', $password)) {
            $this->form_validation->set_message('password_strength_check', 'Password must contain at least one uppercase letter.');
            return false;
        }

        // Check for at least one symbol (special character)
        if (!preg_match('/[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]/', $password)) {
            $this->form_validation->set_message('password_strength_check', 'Password must contain at least one special character.');
            return false;
        }

        // Check for at least one number
        if (!preg_match('/[0-9]/', $password)) {
            $this->form_validation->set_message('password_strength_check', 'Password must contain at least one number.');
            return false;
        }

        return true;
    }
}
