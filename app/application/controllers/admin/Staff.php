<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @property-read Authentication_model $authentication_model
 * @property-read Staff_model $staff_model
 */
class Staff extends AdminController
{
    /* List all staff members */
    public function index()
    {
        if (staff_cant('view', 'staff')) {
            access_denied('staff');
        }
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data('staff');
        }
        $data['staff_members'] = $this->staff_model->get('', ['active' => 1]);
        $data['title']         = _l('staff_members');
        $this->load->view('admin/staff/manage', $data);
    }

    /**
     * ZingHR staff sync dashboard.
     */
    public function sync_dashboard()
    {
        if (staff_cant('view', 'staff')) {
            access_denied('staff');
        }

        $this->load->model('zinghr_sync_model');

        if ($this->input->post()) {
            $this->zinghr_sync_model->save_settings([
                'subscription_name' => $this->input->post('subscription_name', true),
                'token'             => $this->input->post('token', true),
                'from_date'         => $this->input->post('from_date', true),
                'to_date'           => $this->input->post('to_date', true),
            ]);
            set_alert('success', _l('staff_sync_settings_saved'));
            redirect(admin_url('staff/sync_dashboard'));
        }

        $settings = $this->zinghr_sync_model->get_settings();

        $data['title']    = _l('staff_sync_dashboard_title');
        $data['settings'] = $settings;
        $data['zinghr_endpoint'] = 'https://portal.zinghr.com/2015/route/EmployeeDetails/GetEmployeeMasterDetails';

        $this->load->view('admin/staff/sync_process', $data);
    }

    /**
     * AJAX endpoint to trigger ZingHR sync.
     */
    public function run_zinghr_sync()
    {
        if (staff_cant('view', 'staff')) {
            access_denied('staff');
        }

        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $this->load->model('zinghr_sync_model');
        $settings = $this->zinghr_sync_model->get_settings();

        $payload = [
            'subscription_name' => $this->input->post('subscription_name', true) ?: $settings['subscription_name'],
            'token'             => $this->input->post('token', true) ?: $settings['token'],
            'from_date'         => $this->input->post('from_date', true) ?: $settings['from_date'],
            'to_date'           => $this->input->post('to_date', true) ?: $settings['to_date'],
        ];

        $result = $this->zinghr_sync_model->sync($payload);

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($result));
    }

    /* Add new staff member or edit existing */
    public function member($id = '')
    {
        if (staff_cant('view', 'staff')) {
            access_denied('staff');
        }
        hooks()->do_action('staff_member_edit_view_profile', $id);

        $this->load->model('departments_model');
        // Safety: ensure required schema exists (division tables/columns)
        if (!$this->db->field_exists('divisionid', db_prefix().'staff')) {
            $this->db->query('ALTER TABLE `'.db_prefix().'staff` ADD `divisionid` INT(11) NULL DEFAULT NULL AFTER `role`;');
        }
        if (!$this->db->table_exists(db_prefix().'divisions')) {
            $this->db->query('CREATE TABLE `' . db_prefix() . 'divisions` (
                `divisionid` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `name` VARCHAR(191) NOT NULL,
                PRIMARY KEY (`divisionid`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;');
        }

        // Safety: ensure reporting_manager field exists
        if (!$this->db->field_exists('reporting_manager', db_prefix().'staff')) {
            $this->db->query('ALTER TABLE `'.db_prefix().'staff` ADD `reporting_manager` INT(11) NULL DEFAULT NULL AFTER `divisionid`;');
        }
        if ($this->input->post()) {
            $data = $this->input->post();
            // Don't do XSS clean here.
            $data['email_signature'] = $this->input->post('email_signature', false);
            $data['email_signature'] = html_entity_decode($data['email_signature']);

            if ($data['email_signature'] == strip_tags($data['email_signature'])) {
                // not contains HTML, add break lines
                $data['email_signature'] = nl2br_save_html($data['email_signature']);
            }

            $reportAccessInput = $this->input->post('report_access');
            unset($data['report_access']);

            $data['password'] = $this->input->post('password', false);

            if ($id == '') {
                if (staff_cant('create', 'staff')) {
                    access_denied('staff');
                }
                $id = $this->staff_model->add($data);
                if ($id) {
                    $this->save_report_access_preferences($id, $reportAccessInput);
                    handle_staff_profile_image_upload($id);
                    set_alert('success', _l('added_successfully', _l('staff_member')));
                    redirect(admin_url('staff/member/' . $id));
                }
            } else {
                if (staff_cant('edit', 'staff')) {
                    access_denied('staff');
                }
                handle_staff_profile_image_upload($id);
                $response = $this->staff_model->update($data, $id);
                if (is_array($response)) {
                    if (isset($response['cant_remove_main_admin'])) {
                        set_alert('warning', _l('staff_cant_remove_main_admin'));
                    } elseif (isset($response['cant_remove_yourself_from_admin'])) {
                        set_alert('warning', _l('staff_cant_remove_yourself_from_admin'));
                    }
                } elseif ($response == true) {
                    set_alert('success', _l('updated_successfully', _l('staff_member')));
                }
                $this->save_report_access_preferences($id, $reportAccessInput);
                redirect(admin_url('staff/member/' . $id));
            }
        }
        if ($id == '') {
            $title = _l('add_new', _l('staff_member'));
        } else {
            $member = $this->staff_model->get($id);
            if (!$member) {
                blank_page('Staff Member Not Found', 'danger');
            }
            $data['member']            = $member;
            $title                     = $member->firstname . ' ' . $member->lastname;
            $data['staff_departments'] = $this->departments_model->get_staff_departments($member->staffid);

            $ts_filter_data = [];
            if ($this->input->get('filter')) {
                if ($this->input->get('range') != 'period') {
                    $ts_filter_data[$this->input->get('range')] = true;
                } else {
                    $ts_filter_data['period-from'] = $this->input->get('period-from');
                    $ts_filter_data['period-to']   = $this->input->get('period-to');
                }
            } else {
                $ts_filter_data['this_month'] = true;
            }

            $data['logged_time'] = $this->staff_model->get_logged_time_data($id, $ts_filter_data);
            $data['timesheets']  = $data['logged_time']['timesheets'];
        }
        $this->load->model('currencies_model');
        $data['base_currency'] = $this->currencies_model->get_base_currency();
        $data['roles']         = $this->roles_model->get();
        $data['user_notes']    = $this->misc_model->get_notes($id, 'staff');
        $data['departments']   = $this->departments_model->get();
        $this->load->model('divisions_model');
        $data['divisions']     = $this->divisions_model->get();

        // Load staff members for reporting manager dropdown
        $this->db->select('staffid, firstname, lastname');
        $this->db->from(db_prefix() . 'staff');
        $this->db->where('active', 1);
        if ($id != '') {
            $this->db->where('staffid !=', $id); // Don't include self
        }
        $staff_members = $this->db->get()->result_array();

        // Add emp code to each staff member using the same subquery as in Staff_model
        $this->db->select('id');
        $this->db->where('slug', 'staff_emp_code');
        $custom_field = $this->db->get(db_prefix() . 'customfields')->row();

        if ($custom_field) {
            foreach ($staff_members as &$staff) {
                $this->db->select('value as staff_emp_code');
                $this->db->from(db_prefix() . 'customfieldsvalues');
                $this->db->where('relid', $staff['staffid']);
                $this->db->where('fieldid', $custom_field->id);
                $emp_code_result = $this->db->get()->row();
                $staff['staff_emp_code'] = ($emp_code_result) ? $emp_code_result->staff_emp_code : null;
            }
        } else {
            foreach ($staff_members as &$staff) {
                $staff['staff_emp_code'] = null;
            }
        }

        $data['reporting_managers'] = $staff_members;

        $data['title']         = $title;

        $this->load->view('admin/staff/member', $data);
    }

    /**
     * Persist per-staff report access selections into user_meta.
     */
    private function save_report_access_preferences($staffId, $input)
    {
        if (!$staffId) {
            return;
        }
        $normalized = $this->normalize_report_access_input($input);
        update_staff_meta($staffId, 'report_access', json_encode($normalized));
    }

    /**
     * Normalize posted report access payload.
     */
    private function normalize_report_access_input($input)
    {
        if (!is_array($input)) {
            return [];
        }

        $normalized = [];
        foreach ($input as $slug => $config) {
            if (!is_array($config)) {
                continue;
            }
            $global      = !empty($config['global']);
            $own         = !empty($config['own']);
            $divisionIds = [];
            $departmentIds = [];
            $export = !empty($config['export']);

            if (!empty($config['division_enabled']) && isset($config['division_ids']) && is_array($config['division_ids'])) {
                foreach ($config['division_ids'] as $divId) {
                    $divId = (int) $divId;
                    if ($divId > 0) {
                        $divisionIds[] = $divId;
                    }
                }
                $divisionIds = array_values(array_unique($divisionIds));
            }

            if (!empty($config['department_enabled']) && isset($config['department_ids']) && is_array($config['department_ids'])) {
                foreach ($config['department_ids'] as $depId) {
                    $depId = (int) $depId;
                    if ($depId > 0) {
                        $departmentIds[] = $depId;
                    }
                }
                $departmentIds = array_values(array_unique($departmentIds));
            }

            $normalized[$slug] = [
                'global'          => $global,
                'own'             => $own,
                'division_ids'    => $divisionIds,
                'department_ids'  => $departmentIds,
                'export'          => $export,
            ];
        }

        return $normalized;
    }

    /* Get role permission for specific role id */
    public function role_changed($id)
    {
        if (staff_cant('view', 'staff')) {
            ajax_access_denied('staff');
        }

        echo json_encode($this->roles_model->get($id)->permissions);
    }

    public function save_dashboard_widgets_order()
    {
        hooks()->do_action('before_save_dashboard_widgets_order');

        $post_data = $this->input->post();
        foreach ($post_data as $container => $widgets) {
            if ($widgets == 'empty') {
                $post_data[$container] = [];
            }
        }
        update_staff_meta(get_staff_user_id(), 'dashboard_widgets_order', serialize($post_data));
    }

    public function save_dashboard_widgets_visibility()
    {
        hooks()->do_action('before_save_dashboard_widgets_visibility');

        $post_data = $this->input->post();
        update_staff_meta(get_staff_user_id(), 'dashboard_widgets_visibility', serialize($post_data['widgets']));
    }

    public function reset_dashboard()
    {
        update_staff_meta(get_staff_user_id(), 'dashboard_widgets_visibility', null);
        update_staff_meta(get_staff_user_id(), 'dashboard_widgets_order', null);

        redirect(admin_url());
    }

    public function save_hidden_table_columns()
    {
        hooks()->do_action('before_save_hidden_table_columns');
        $data   = $this->input->post();
        $id     = $data['id'];
        $hidden = isset($data['hidden']) ? $data['hidden'] : [];
        update_staff_meta(get_staff_user_id(), 'hidden-columns-' . $id, json_encode($hidden));
    }

    public function change_language($lang = '')
    {
        hooks()->do_action('before_staff_change_language', $lang);

        $this->db->where('staffid', get_staff_user_id());
        $this->db->update(db_prefix() . 'staff', ['default_language' => $lang]);
        
        redirect(previous_url() ?: $_SERVER['HTTP_REFERER']);
    }

    public function timesheets()
    {
        if (!is_admin()) {
            $meta = get_staff_meta(get_staff_user_id(), 'report_access');
            $reportAccess = is_string($meta) ? json_decode($meta, true) : [];
            if (!is_array($reportAccess)) {
                $reportAccess = [];
            }
            $config = $reportAccess['timesheets'] ?? [];
            $hasAccess = false;
            if (!empty($config['global']) || !empty($config['own'])) {
                $hasAccess = true;
            }
            if (!empty($config['division_ids']) && is_array($config['division_ids'])) {
                $hasAccess = true;
            }
            if (!empty($config['department_ids']) && is_array($config['department_ids'])) {
                $hasAccess = true;
            }
            if (!$hasAccess) {
                access_denied('reports');
            }
        }

        $data['view_all'] = false;
        if (staff_can('view-timesheets', 'reports') && $this->input->get('view') == 'all') {
            $data['staff_members_with_timesheets'] = $this->db->query('SELECT DISTINCT staff_id FROM ' . db_prefix() . 'taskstimers WHERE staff_id !=' . get_staff_user_id())->result_array();
            $data['view_all']                      = true;
        }

        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data('staff_timesheets', ['view_all' => $data['view_all']]);
        }

        if ($data['view_all'] == false) {
            unset($data['view_all']);
        }

        $data['logged_time'] = $this->staff_model->get_logged_time_data(get_staff_user_id());
        $data['title']       = '';
        $this->load->view('admin/staff/timesheets', $data);
    }

    public function delete()
    {
        if (!is_admin() && is_admin($this->input->post('id'))) {
            die('Busted, you can\'t delete administrators');
        }

        if (staff_can('delete',  'staff')) {
            $success = $this->staff_model->delete($this->input->post('id'), $this->input->post('transfer_data_to'));
            if ($success) {
                set_alert('success', _l('deleted', _l('staff_member')));
            }
        }

        redirect(admin_url('staff'));
    }

    /* When staff edit his profile */
    public function edit_profile()
    {
        hooks()->do_action('edit_logged_in_staff_profile');

        if ($this->input->post()) {
            handle_staff_profile_image_upload();
            $data = $this->input->post();
            // Don't do XSS clean here.
            $data['email_signature'] = $this->input->post('email_signature', false);
            $data['email_signature'] = html_entity_decode($data['email_signature']);

            if ($data['email_signature'] == strip_tags($data['email_signature'])) {
                // not contains HTML, add break lines
                $data['email_signature'] = nl2br_save_html($data['email_signature']);
            }

            $success = $this->staff_model->update_profile($data, get_staff_user_id());

            if ($success) {
                set_alert('success', _l('staff_profile_updated'));
            }

            redirect(admin_url('staff/edit_profile/' . get_staff_user_id()));
        }
        $member = $this->staff_model->get(get_staff_user_id());
        $this->load->model('departments_model');
        $data['member']            = $member;
        $data['departments']       = $this->departments_model->get();
        $data['staff_departments'] = $this->departments_model->get_staff_departments($member->staffid);
        $data['title']             = $member->firstname . ' ' . $member->lastname;
        $this->load->view('admin/staff/profile', $data);
    }

    /* Remove staff profile image / ajax */
    public function remove_staff_profile_image($id = '')
    {
        $staff_id = get_staff_user_id();
        if (is_numeric($id) && (staff_can('create',  'staff') || staff_can('edit',  'staff'))) {
            $staff_id = $id;
        }
        hooks()->do_action('before_remove_staff_profile_image');
        $member = $this->staff_model->get($staff_id);
        if (file_exists(get_upload_path_by_type('staff') . $staff_id)) {
            delete_dir(get_upload_path_by_type('staff') . $staff_id);
        }
        $this->db->where('staffid', $staff_id);
        $this->db->update(db_prefix() . 'staff', [
            'profile_image' => null,
        ]);

        if (!is_numeric($id)) {
            redirect(admin_url('staff/edit_profile/' . $staff_id));
        } else {
            redirect(admin_url('staff/member/' . $staff_id));
        }
    }

    /* When staff change his password */
    public function change_password_profile()
    {
        if ($this->input->post()) {
            $response = $this->staff_model->change_password($this->input->post(null, false), get_staff_user_id());
            if (is_array($response) && isset($response[0]['passwordnotmatch'])) {
                set_alert('danger', _l('staff_old_password_incorrect'));
            } else {
                if ($response == true) {
                    set_alert('success', _l('staff_password_changed'));
                } else {
                    set_alert('warning', _l('staff_problem_changing_password'));
                }
            }
            redirect(admin_url('staff/edit_profile'));
        }
    }

    /* Force password change for new users */
    public function force_password_change()
    {
        $this->load->library('form_validation');

        // Check if user has already changed password (welcome_popup_shown = 1)
        $this->db->select('welcome_popup_shown');
        $this->db->where('staffid', get_staff_user_id());
        $staff = $this->db->get(db_prefix() . 'staff')->row();

        // If password has been changed (flag = 1), redirect to dashboard
        if ($staff && $staff->welcome_popup_shown == 1) {
            redirect(admin_url());
        }

        if ($this->input->post()) {
            $this->form_validation->set_rules('password', _l('staff_password'), 'required');
            $this->form_validation->set_rules('passwordr', _l('staff_password_repeat'), 'required|matches[password]');

            if ($this->form_validation->run() !== false) {
                $password = $this->input->post('password', false);

                // Update password
                $this->db->where('staffid', get_staff_user_id());
                $this->db->update(db_prefix() . 'staff', [
                    'password' => app_hash_password($password),
                    'last_password_change' => date('Y-m-d H:i:s')
                ]);

                // Update welcome popup flag to indicate password has been changed
                $this->db->where('staffid', get_staff_user_id());
                $this->db->update(db_prefix() . 'staff', ['welcome_popup_shown' => 1]);

                log_message('info', 'User ' . get_staff_user_id() . ' completed forced password change');

                set_alert('success', _l('staff_password_changed'));
                redirect(admin_url());
            }
        }

        $data['title'] = 'Set Your Password';
        $this->load->view('admin/staff/force_password_change', $data);
    }

    /* View public profile. If id passed view profile by staff id else current user*/
    public function profile($id = '')
    {
        if ($id == '') {
            $id = get_staff_user_id();
        }

        hooks()->do_action('staff_profile_access', $id);

        $data['logged_time'] = $this->staff_model->get_logged_time_data($id);
        $data['staff_p']     = $this->staff_model->get($id);

        if (!$data['staff_p']) {
            blank_page('Staff Member Not Found', 'danger');
        }

        $this->load->model('departments_model');
        $data['staff_departments'] = $this->departments_model->get_staff_departments($data['staff_p']->staffid);
        $data['departments']       = $this->departments_model->get();
        $data['title']             = _l('staff_profile_string') . ' - ' . $data['staff_p']->firstname . ' ' . $data['staff_p']->lastname;
        // notifications
        $total_notifications = total_rows(db_prefix() . 'notifications', [
            'touserid' => get_staff_user_id(),
        ]);
        $data['total_pages'] = ceil($total_notifications / $this->misc_model->get_notifications_limit());
        $this->load->view('admin/staff/myprofile', $data);
    }

    /* Change status to staff active or inactive / ajax */
    public function change_staff_status($id, $status)
    {
        if (staff_can('edit',  'staff')) {
            if ($this->input->is_ajax_request()) {
                $this->staff_model->change_staff_status($id, $status);
            }
        }
    }

    /* Logged in staff notifications*/
    public function notifications()
    {
        $this->load->model('misc_model');
        if ($this->input->post()) {
            $page   = $this->input->post('page');
            $offset = ($page * $this->misc_model->get_notifications_limit());
            $this->db->limit($this->misc_model->get_notifications_limit(), $offset);
            $this->db->where('touserid', get_staff_user_id());
            $this->db->order_by('date', 'desc');
            $notifications = $this->db->get(db_prefix() . 'notifications')->result_array();
            $i             = 0;
            foreach ($notifications as $notification) {
                if (($notification['fromcompany'] == null && $notification['fromuserid'] != 0) || ($notification['fromcompany'] == null && $notification['fromclientid'] != 0)) {
                    if ($notification['fromuserid'] != 0) {
                        $notifications[$i]['profile_image'] = '<a href="' . admin_url('staff/profile/' . $notification['fromuserid']) . '">' . staff_profile_image($notification['fromuserid'], [
                            'staff-profile-image-small',
                            'img-circle',
                            'pull-left',
                        ]) . '</a>';
                    } else {
                        $notifications[$i]['profile_image'] = '<a href="' . admin_url('clients/client/' . $notification['fromclientid']) . '">
                    <img class="client-profile-image-small img-circle pull-left" src="' . contact_profile_image_url($notification['fromclientid']) . '"></a>';
                    }
                } else {
                    $notifications[$i]['profile_image'] = '';
                    $notifications[$i]['full_name']     = '';
                }
                $additional_data = '';
                if (!empty($notification['additional_data'])) {
                    $additional_data = unserialize($notification['additional_data']);
                    $x               = 0;
                    foreach ($additional_data as $data) {
                        if (strpos($data, '<lang>') !== false) {
                            $lang = get_string_between($data, '<lang>', '</lang>');
                            $temp = _l($lang);
                            if (strpos($temp, 'project_status_') !== false) {
                                $status = get_project_status_by_id(strafter($temp, 'project_status_'));
                                $temp   = $status['name'];
                            }
                            $additional_data[$x] = $temp;
                        }
                        $x++;
                    }
                }
                $notifications[$i]['description'] = _l($notification['description'], $additional_data);
                $notifications[$i]['date']        = time_ago($notification['date']);
                $notifications[$i]['full_date']   = _dt($notification['date']);
                $i++;
            } //$notifications as $notification
            echo json_encode($notifications);
            die;
        }
    }

    public function update_two_factor()
    {
        $fail_reason = _l('set_two_factor_authentication_failed');
        if ($this->input->post()) {
            $this->load->library('form_validation');
            $this->form_validation->set_rules('two_factor_auth', _l('two_factor_auth'), 'required');

            if ($this->input->post('two_factor_auth') == 'google') {
                $this->form_validation->set_rules('google_auth_code', _l('google_authentication_code'), 'required');
            }

            if ($this->form_validation->run() !== false) {
                $two_factor_auth_mode = $this->input->post('two_factor_auth');
                $id = get_staff_user_id();
                if ($two_factor_auth_mode == 'google') {
                    $this->load->model('Authentication_model');
                    $secret = $this->input->post('secret');
                    $success = $this->authentication_model->set_google_two_factor($secret);
                    $fail_reason = _l('set_google_two_factor_authentication_failed');
                } elseif ($two_factor_auth_mode == 'email') {
                    $this->db->where('staffid', $id);
                    $success = $this->db->update(db_prefix() . 'staff', ['two_factor_auth_enabled' => 1]);
                } else {
                    $this->db->where('staffid', $id);
                    $success = $this->db->update(db_prefix() . 'staff', ['two_factor_auth_enabled' => 0]);
                }
                if ($success) {
                    set_alert('success', _l('set_two_factor_authentication_successful'));
                    redirect(admin_url('staff/edit_profile/' . get_staff_user_id()));
                }
            }
        }
        set_alert('danger', $fail_reason);
        redirect(admin_url('staff/edit_profile/' . get_staff_user_id()));
    }

    public function verify_google_two_factor()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
            die;
        }

        if ($this->input->post()) {
            $data = $this->input->post();
            $this->load->model('authentication_model');
            $is_success = $this->authentication_model->is_google_two_factor_code_valid($data['code'],$data['secret']);
            $result = [];

            header('Content-Type: application/json');
            if ($is_success) {
                $result['status'] = 'success';
                $result['message'] = _l('google_2fa_code_valid');;

                echo json_encode($result);
                die;
            }

            $result['status'] = 'failed';
            $result['message'] = _l('google_2fa_code_invalid');;

            echo json_encode($result);
            die;
        }
    }

    public function save_completed_checklist_visibility()
    {
        hooks()->do_action('before_save_completed_checklist_visibility');

        $post_data = $this->input->post();
        if (is_numeric($post_data['task_id'])) {
            update_staff_meta(get_staff_user_id(), 'task-hide-completed-items-'. $post_data['task_id'], $post_data['hideCompleted']);
        }
    }

    /* Bulk upload staff members */
    public function bulk_upload()
    {
        if (!staff_can('create', 'staff')) {
            access_denied('staff');
        }

        if ($this->input->post()) {
            // Check if this is confirmed data from preview
            $confirmed_data = $this->input->post('confirmed_data');
            if ($confirmed_data) {
                try {
                    // Process confirmed data from preview - Step 1: Create staff
                    $result = $this->process_staff_creation_only($confirmed_data);

                    if ($result['success']) {
                        set_alert('success', 'Successfully created ' . $result['imported'] . ' staff members. Now proceed to update reporting managers.');
                        log_message('info', 'Bulk upload step 1 successful: ' . $result['imported'] . ' staff members created');
                        // Store the import data and created staff info in session for step 2
                        $this->session->set_userdata('bulk_upload_pending_managers', $confirmed_data);
                        $this->session->set_userdata('bulk_upload_created_staff', $result['created_staff']);
                    } else {
                        set_alert('danger', 'Staff creation failed: ' . $result['error']);
                        log_message('error', 'Bulk upload step 1 failed: ' . $result['error']);
                    }
                } catch (Exception $e) {
                    set_alert('danger', 'Import failed due to system error: ' . $e->getMessage());
                    log_message('error', 'Bulk upload exception: ' . $e->getMessage());
                }

                redirect(admin_url('staff/bulk_upload'));
            } elseif ($this->input->post('update_managers')) {
                try {
                    // Step 2: Update reporting managers
                    $pending_data = $this->session->userdata('bulk_upload_pending_managers');
                    if ($pending_data) {
                        $result = $this->process_reporting_manager_updates_only($pending_data);

                        if ($result['success']) {
                            // Store success message in session for popup display
                            $this->session->set_userdata('bulk_upload_complete', [
                                'success' => true,
                                'message' => 'Bulk upload completed successfully! Created ' . $result['updated'] . ' staff members with reporting managers updated.',
                                'updated' => $result['updated']
                            ]);
                            log_message('info', 'Bulk upload step 2 successful: ' . $result['updated'] . ' reporting managers updated');
                            // Clear the session data
                            $this->session->unset_userdata('bulk_upload_pending_managers');
                            $this->session->unset_userdata('bulk_upload_created_staff');

                            // Return JSON response for AJAX requests
                            if ($this->input->is_ajax_request()) {
                                header('Content-Type: application/json');
                                echo json_encode([
                                    'success' => true,
                                    'message' => 'Bulk upload completed successfully! Created ' . $result['updated'] . ' staff members with reporting managers updated.',
                                    'updated' => $result['updated']
                                ]);
                                exit;
                            }
                        } else {
                            // Return JSON error response for AJAX requests
                            if ($this->input->is_ajax_request()) {
                                header('Content-Type: application/json');
                                echo json_encode([
                                    'success' => false,
                                    'error' => $result['error']
                                ]);
                                exit;
                            }
                            set_alert('danger', 'Reporting manager update failed: ' . $result['error']);
                            log_message('error', 'Bulk upload step 2 failed: ' . $result['error']);
                        }
                    } else {
                        // Return JSON error response for AJAX requests
                        if ($this->input->is_ajax_request()) {
                            header('Content-Type: application/json');
                            echo json_encode([
                                'success' => false,
                                'error' => 'No pending reporting manager updates found. Please start the bulk upload process again.'
                            ]);
                            exit;
                        }
                        set_alert('warning', 'No pending reporting manager updates found. Please start the bulk upload process again.');
                    }
                } catch (Exception $e) {
                    // Return JSON error response for AJAX requests
                    if ($this->input->is_ajax_request()) {
                        header('Content-Type: application/json');
                        echo json_encode([
                            'success' => false,
                            'error' => 'Reporting manager update failed due to system error: ' . $e->getMessage()
                        ]);
                        exit;
                    }
                    set_alert('danger', 'Reporting manager update failed due to system error: ' . $e->getMessage());
                    log_message('error', 'Bulk upload step 2 exception: ' . $e->getMessage());
                }

                redirect(admin_url('staff/bulk_upload'));
            } else {
                // Handle file upload for preview - this should not happen in the current implementation
                // The preview is handled client-side, so this branch should not be reached
                redirect(admin_url('staff/bulk_upload'));
            }
        }

        $data['title'] = 'Bulk Upload Staff';
        $data['has_pending_managers'] = $this->session->userdata('bulk_upload_pending_managers') ? true : false;
        $data['created_staff'] = $this->session->userdata('bulk_upload_created_staff') ?: [];
        $this->load->view('admin/staff/bulk_upload', $data);
    }

    private function process_bulk_upload($file)
    {
        $file_path = $file['tmp_name'];
        $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);

        $data = [];
        $errors = [];
        $imported = 0;
        $skipped = 0;

        try {
            if (strtolower($file_extension) == 'csv') {
                $data = $this->parse_csv($file_path);
            } else {
                // For Excel files, we'd need PHPExcel or similar library
                return ['success' => false, 'error' => _l('excel_not_supported')];
            }

            if (empty($data)) {
                return ['success' => false, 'error' => _l('no_data_found')];
            }

            // Validate minimum rows (header + at least 1 data row)
            if (count($data) < 2) {
                return ['success' => false, 'error' => _l('insufficient_data_rows')];
            }

            // Skip header row
            $header = array_shift($data);

            // Validate header
            $validation_result = $this->validate_csv_header($header);
            if (!$validation_result['valid']) {
                return ['success' => false, 'error' => $validation_result['error']];
            }

            // Start transaction for data consistency
            $this->db->trans_start();

            foreach ($data as $row_index => $row) {
                if (empty(array_filter($row))) {
                    $skipped++;
                    continue; // Skip empty rows
                }

                // Validate row data
                $validation_result = $this->validate_csv_row($row, $header, $row_index + 2);
                if (!$validation_result['valid']) {
                    $errors[] = $validation_result['error'];
                    continue;
                }

                $staff_data = $this->map_csv_row_to_staff_data($row, $header);

                if ($staff_data) {
                    try {
                        log_message('debug', 'About to call staff_model->add with data: ' . json_encode($staff_data));
                        $result = $this->staff_model->add($staff_data);
                        log_message('debug', 'staff_model->add returned: ' . ($result ? $result : 'false/null'));
                        if ($result) {
                            $imported++;
                        } else {
                            $errors[] = "Row " . ($row_index + 2) . ": " . _l('failed_to_create_staff');
                        }
                    } catch (Exception $e) {
                        $errors[] = "Row " . ($row_index + 2) . ": " . $e->getMessage();
                        log_message('error', 'Staff creation error: ' . $e->getMessage());
                    }
                } else {
                    $errors[] = "Row " . ($row_index + 2) . ": " . _l('invalid_data_format');
                }

                // Prevent processing too many rows at once (max 1000)
                if (($imported + count($errors)) >= 1000) {
                    $errors[] = _l('too_many_rows');
                    break;
                }
            }

            // Complete transaction
            if (empty($errors)) {
                $this->db->trans_complete();
                if ($this->db->trans_status() === FALSE) {
                    return ['success' => false, 'error' => _l('transaction_failed')];
                }
            } else {
                $this->db->trans_rollback();
                // Clean error messages for JSON response
                $clean_errors = array_map(function($error) {
                    return strip_tags(str_replace(['<br>', '<br/>', '<br />'], ' | ', $error));
                }, array_slice($errors, 0, 10));

                return [
                    'success' => false,
                    'error' => 'Staff creation failed. See details below.',
                    'detailed_errors' => $clean_errors,
                    'error_count' => count($errors)
                ];
            }

        } catch (Exception $e) {
            log_message('error', 'Bulk upload processing error: ' . $e->getMessage());
            return ['success' => false, 'error' => _l('processing_error') . ': ' . $e->getMessage()];
        }

        $message = _l('bulk_upload_success', $imported);
        if ($skipped > 0) {
            $message .= '<br>' . _l('rows_skipped', $skipped);
        }

        return ['success' => true, 'imported' => $imported, 'skipped' => $skipped, 'message' => $message];
    }

    private function parse_csv($file_path)
    {
        $data = [];
        if (($handle = fopen($file_path, "r")) !== false) {
            while (($row = fgetcsv($handle, 1000, ",")) !== false) {
                $data[] = $row;
            }
            fclose($handle);
        }
        return $data;
    }

    private function normalize_csv_column_name($column_name)
    {
        $normalized = strtolower(trim($column_name));
        $normalized = preg_replace('/[^a-z0-9]+/', '_', $normalized);
        $normalized = trim($normalized, '_');

        // Provide common aliases so older CSV templates and different locales map correctly
        static $aliases = [
            'first_name' => 'firstname',
            'lastname' => 'lastname',
            'last_name' => 'lastname',
            'e_mail' => 'email',
            'email_address' => 'email',
            'mobile' => 'phone',
            'mobile_number' => 'phone',
            'phone_number' => 'phone',
            'department_name' => 'department',
            'subdepartment' => 'sub_department',
            'sub_department_name' => 'sub_department',
            'subdepartment_name' => 'sub_department',
            'division_name' => 'division',
        ];

        if (isset($aliases[$normalized])) {
            return $aliases[$normalized];
        }

        $stripped = str_replace('_', '', $normalized);
        if (isset($aliases[$stripped])) {
            return $aliases[$stripped];
        }

        return $normalized;
    }

    private function map_csv_row_to_staff_data($row, $header)
    {
        // Expected CSV columns: firstname,lastname,email,password,role,department,division,phone,active,reporting_manager,sub_department,send_welcome_email
        $data = [];

        // Map by column names or positions
        $column_map = [
            'firstname' => null,
            'lastname' => null,
            'email' => null,
            'emp_code' => null,
            'password' => null,
            'role' => null,
            'department' => null,
            'division' => null,
            'phone' => null,
            'active' => null,
            'reporting_manager' => null,
            'sub_department' => null,
            'send_welcome_email' => null
        ];

        // Try to map by header names first
        if ($header) {
            foreach ($header as $index => $column_name) {
                $normalized = $this->normalize_csv_column_name($column_name);
                if (array_key_exists($normalized, $column_map)) {
                    $column_map[$normalized] = $index;
                }
            }
        }

        // If header mapping failed, assume standard order
        $mapped_columns = array_filter($column_map, function ($value) {
            return $value !== null;
        });

        if (count($mapped_columns) < 3) { // At least firstname, emp_code, email
            $column_map = [
                'firstname' => 0,
                'lastname' => 1,
                'email' => 2,
                'emp_code' => 3,
                'password' => 4,
                'role' => 5,
                'department' => 6,
                'division' => 7,
                'phone' => 8,
                'active' => 9,
                'reporting_manager' => 10,
                'sub_department' => 11,
                'send_welcome_email' => 12
            ];
        }

        // Extract data
        $firstname = isset($column_map['firstname']) && isset($row[$column_map['firstname']]) ? trim($row[$column_map['firstname']]) : '';
        $lastname = isset($column_map['lastname']) && isset($row[$column_map['lastname']]) ? trim($row[$column_map['lastname']]) : '';
        $email = isset($column_map['email']) && isset($row[$column_map['email']]) ? trim($row[$column_map['email']]) : '';
        $emp_code = isset($column_map['emp_code']) && isset($row[$column_map['emp_code']]) ? trim($row[$column_map['emp_code']]) : '';
        $password = isset($column_map['password']) && isset($row[$column_map['password']]) ? trim($row[$column_map['password']]) : 'password123'; // Default password
        $role = isset($column_map['role']) && isset($row[$column_map['role']]) ? trim($row[$column_map['role']]) : '';
        $department = isset($column_map['department']) && isset($row[$column_map['department']]) ? trim($row[$column_map['department']]) : '';
        $division = isset($column_map['division']) && isset($row[$column_map['division']]) ? trim($row[$column_map['division']]) : '';
        $phone = isset($column_map['phone']) && isset($row[$column_map['phone']]) ? trim($row[$column_map['phone']]) : '';
        $active = isset($column_map['active']) && isset($row[$column_map['active']]) ? trim($row[$column_map['active']]) : '1';
        $reporting_manager = isset($column_map['reporting_manager']) && isset($row[$column_map['reporting_manager']]) ? trim($row[$column_map['reporting_manager']]) : '';
        $sub_department = isset($column_map['sub_department']) && isset($row[$column_map['sub_department']]) ? trim($row[$column_map['sub_department']]) : '';
        $send_welcome_email = isset($column_map['send_welcome_email']) && isset($row[$column_map['send_welcome_email']]) ? trim($row[$column_map['send_welcome_email']]) : '';

        // Validate required fields
        if (empty($firstname) || empty($emp_code)) {
            return false;
        }

        // Build staff data array
        $data = [
            'firstname' => $firstname,
            'lastname' => $lastname,
            'email' => $email,
            'emp_code' => $emp_code, // Add emp_code to data array
            'password' => $password,
            'active' => $active == '1' || strtolower($active) == 'yes' || strtolower($active) == 'active' ? 1 : 0,
            'send_welcome_email' => $send_welcome_email == '1' || strtolower($send_welcome_email) == 'yes' || strtolower($send_welcome_email) == 'true' ? true : false,
        ];

        // Handle role
        if (!empty($role)) {
            $role_id = $this->get_role_id_by_name($role);
            if ($role_id) {
                $data['role'] = $role_id;
            }
        }

        // Handle departments - prioritize sub department (child) like the UI does
        $departments = [];
        $dept_id = null;
        $sub_dept_id = null;

        if (!empty($department)) {
            $dept_id = $this->get_department_id_by_name($department);
        }

        if (!empty($sub_department)) {
            $sub_dept_id = $this->get_department_id_by_name($sub_department);
        }

        if ($sub_dept_id) {
            $departments[] = $sub_dept_id;
        }

        if ($dept_id && (!$sub_dept_id || $dept_id !== $sub_dept_id)) {
            $departments[] = $dept_id;
        }

        if (!empty($departments)) {
            $data['departments'] = array_values(array_unique($departments));
        }

        // Handle division
        if (!empty($division)) {
            $div_id = $this->get_division_id_by_name($division);
            if ($div_id) {
                $data['divisionid'] = $div_id;
            }
        }

        // Handle phone
        if (!empty($phone)) {
            $data['phonenumber'] = $phone;
        }

        // Handle reporting manager - will be set after all staff are created
        // Don't look up here as not all staff may be created yet
        $data['reporting_manager'] = $reporting_manager;

        return $data;
    }

    private function get_role_id_by_name($role_name)
    {
        $this->db->select('roleid');
        $this->db->from(db_prefix() . 'roles');
        $this->db->where('name', $role_name);
        $result = $this->db->get()->row();
        return $result ? $result->roleid : null;
    }

    private function get_department_id_by_name($dept_name)
    {
        $this->db->select('departmentid');
        $this->db->from(db_prefix() . 'departments');
        $this->db->where('name', $dept_name);
        $result = $this->db->get()->row();
        return $result ? $result->departmentid : null;
    }

    private function get_division_id_by_name($div_name)
    {
        $this->db->select('divisionid');
        $this->db->from(db_prefix() . 'divisions');
        $this->db->where('name', $div_name);
        $result = $this->db->get()->row();
        return $result ? $result->divisionid : null;
    }

    private function get_staff_id_by_name($staff_name)
    {
        // Try to match by full name (firstname + lastname)
        $name_parts = explode(' ', trim($staff_name), 2);
        if (count($name_parts) == 2) {
            $this->db->select('staffid');
            $this->db->from(db_prefix() . 'staff');
            $this->db->where('firstname', $name_parts[0]);
            $this->db->where('lastname', $name_parts[1]);
            $result = $this->db->get()->row();
            if ($result) {
                return $result->staffid;
            }
        }

        // Try to match by email
        $this->db->select('staffid');
        $this->db->from(db_prefix() . 'staff');
        $this->db->where('email', $staff_name);
        $result = $this->db->get()->row();
        return $result ? $result->staffid : null;
    }

    private function get_staff_id_by_name_or_empcode($staff_identifier)
    {
        $staff_identifier = trim($staff_identifier);

        // First try to match by employee code - take the latest staff ID (highest ID)
        $this->db->select('cfv.relid as staffid');
        $this->db->from(db_prefix() . 'customfields cf');
        $this->db->join(db_prefix() . 'customfieldsvalues cfv', 'cf.id = cfv.fieldid');
        $this->db->where('cf.slug', 'staff_emp_code');
        $this->db->where('cfv.value', $staff_identifier);
        $this->db->order_by('cfv.relid', 'DESC'); // Order by staffid DESC to get the latest
        $this->db->limit(1); // Take only the first (latest) result
        $result = $this->db->get()->row();
        if ($result) {
            return $result->staffid;
        }

        // If not found by emp code, try name/email matching
        return $this->get_staff_id_by_name($staff_identifier);
    }

    private function validate_csv_header($header)
    {
        if (empty($header) || count($header) < 2) {
            return ['valid' => false, 'error' => _l('invalid_csv_header')];
        }

        $required_columns = ['firstname', 'emp_code'];
        $found_required = 0;

        foreach ($header as $column) {
            $column = $this->normalize_csv_column_name($column);
            if (in_array($column, $required_columns)) {
                $found_required++;
            }
        }

        if ($found_required < 2) {
            return ['valid' => false, 'error' => _l('missing_required_columns')];
        }

        return ['valid' => true];
    }

    private function validate_csv_row($row, $header, $row_number)
    {
        // Check if row has enough columns
        if (count($row) < count($header)) {
            return ['valid' => false, 'error' => "Row {$row_number}: Insufficient columns in CSV row"];
        }

        // Map columns
        $column_map = [];
        foreach ($header as $index => $column_name) {
            $normalized = $this->normalize_csv_column_name($column_name);
            $column_map[$normalized] = $index;
        }

        // Validate required fields
        $firstname = isset($column_map['firstname']) && isset($row[$column_map['firstname']]) ? trim($row[$column_map['firstname']]) : '';
        $lastname = isset($column_map['lastname']) && isset($row[$column_map['lastname']]) ? trim($row[$column_map['lastname']]) : '';
        $email = isset($column_map['email']) && isset($row[$column_map['email']]) ? trim($row[$column_map['email']]) : '';
        $emp_code = isset($column_map['emp_code']) && isset($row[$column_map['emp_code']]) ? trim($row[$column_map['emp_code']]) : '';

        if (empty($firstname)) {
            return ['valid' => false, 'error' => "Row {$row_number}: First name is required"];
        }

        if (empty($emp_code)) {
            return ['valid' => false, 'error' => "Row {$row_number}: Employee code is required"];
        }

        // Validate email format if provided
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['valid' => false, 'error' => "Row {$row_number}: Invalid email format"];
        }

        // Check for duplicate email in database if email is provided
        if (!empty($email)) {
            // Use more specific query to avoid false positives
            $this->db->select('staffid, email');
            $this->db->from(db_prefix() . 'staff');
            $this->db->where('email', $email);
            $this->db->where('active', 1); // Only check active staff
            $existing_staff = $this->db->get()->row();

            if ($existing_staff) {
                return ['valid' => false, 'error' => "Row {$row_number}: Email '{$email}' already exists for active staff member"];
            }
        }

        // Check for duplicate emp_code in database
        if (!empty($emp_code)) {
            $this->db->select('cfv.relid as staffid, cfv.value as emp_code');
            $this->db->from(db_prefix() . 'customfields cf');
            $this->db->join(db_prefix() . 'customfieldsvalues cfv', 'cf.id = cfv.fieldid');
            $this->db->join(db_prefix() . 'staff s', 'cfv.relid = s.staffid');
            $this->db->where('cf.slug', 'staff_emp_code');
            $this->db->where('cfv.value', $emp_code);
            $this->db->where('s.active', 1); // Only check active staff
            $existing_emp_code = $this->db->get()->row();

            if ($existing_emp_code) {
                return ['valid' => false, 'error' => "Row {$row_number}: Employee code '{$emp_code}' already exists for active staff member"];
            }
        }

        // Validate name lengths
        if (strlen($firstname) > 50) {
            return ['valid' => false, 'error' => "Row {$row_number}: First name is too long (maximum 50 characters)"];
        }

        if (strlen($lastname) > 50) {
            return ['valid' => false, 'error' => "Row {$row_number}: Last name is too long (maximum 50 characters)"];
        }

        // Validate phone if provided
        $phone = isset($column_map['phone']) && isset($row[$column_map['phone']]) ? trim($row[$column_map['phone']]) : '';
        if (!empty($phone) && strlen($phone) > 20) {
            return ['valid' => false, 'error' => "Row {$row_number}: Phone number is too long (maximum 20 characters)"];
        }

        return ['valid' => true];
    }

    private function process_staff_creation_only($confirmed_data_json)
    {
        try {
            $confirmed_data = json_decode($confirmed_data_json, true);

            if (!$confirmed_data || !isset($confirmed_data['headers']) || !isset($confirmed_data['data'])) {
                return ['success' => false, 'error' => _l('invalid_data_format')];
            }

            // Load required models
            $this->load->model('departments_model');
            $this->load->model('divisions_model');

            $headers = $confirmed_data['headers'];
            $data = $confirmed_data['data'];
            $errors = [];
            $imported = 0;
            $staff_to_email = []; // Track which staff need welcome emails
            $created_staff = []; // Track created staff for step 2 display

            // Start transaction for data consistency
            $this->db->trans_start();

            foreach ($data as $row_index => $row) {
                $staff_data = $this->map_csv_row_to_staff_data($row, $headers);

                if ($staff_data) {
                    try {
                        // Extract emp_code, departments, and send_welcome_email flags before removing them from staff_data
                        // Note: reporting_manager is completely ignored in this step
                        $emp_code = isset($staff_data['emp_code']) ? $staff_data['emp_code'] : '';
                        $reporting_manager = isset($staff_data['reporting_manager']) ? $staff_data['reporting_manager'] : '';
                        $departments = isset($staff_data['departments']) ? $staff_data['departments'] : [];
                        $send_welcome_email = isset($staff_data['send_welcome_email']) ? $staff_data['send_welcome_email'] : false;

                        // Remove fields that need special handling
                        unset($staff_data['emp_code']); // Remove from data array as it's stored as custom field
                        unset($staff_data['reporting_manager']); // Completely ignore reporting manager in step 1
                        unset($staff_data['departments']); // Will be handled by Staff_model->add()
                        unset($staff_data['send_welcome_email']); // Remove from data array as it's not a DB field

                        // Add departments back to staff_data for Staff_model->add() to handle
                        if (!empty($departments)) {
                            $staff_data['departments'] = $departments;
                        }

                        $result = $this->staff_model->add($staff_data);
                        if ($result) {
                            // Save emp_code as custom field
                            if (!empty($emp_code)) {
                                $this->save_staff_emp_code($result, $emp_code);
                            }

                            $imported++;

                            // Track staff who need welcome emails
                            if ($send_welcome_email) {
                                $staff_to_email[] = $result;
                            }

                            // Track created staff for step 2 display
                            // Note: manager_info will be resolved after all staff are created
                            $created_staff[] = [
                                'staff_id' => $result,
                                'emp_code' => $emp_code,
                                'reporting_manager' => $reporting_manager,
                                'manager_info' => null, // Will be resolved later
                                'firstname' => $staff_data['firstname'],
                                'lastname' => $staff_data['lastname']
                            ];
                        } else {
                            $errors[] = "Row " . ($row_index + 1) . ": " . _l('failed_to_create_staff');
                        }
                    } catch (Exception $e) {
                        $error_message = strip_tags($e->getMessage());
                        $error_message = str_replace(['<br>', '<br/>', '<br />'], ' | ', $error_message);
                        $errors[] = "Row " . ($row_index + 1) . ": " . $error_message;
                        log_message('error', 'Staff creation error: ' . $e->getMessage());
                    }
                } else {
                    $errors[] = "Row " . ($row_index + 1) . ": " . _l('invalid_data_format');
                }
            }

            // Complete transaction
            if (empty($errors)) {
                $this->db->trans_complete();
                if ($this->db->trans_status() === FALSE) {
                    return ['success' => false, 'error' => _l('transaction_failed')];
                }

                // Send welcome emails to staff who requested them
                if (!empty($staff_to_email)) {
                    $this->send_bulk_welcome_emails($staff_to_email);
                }
            } else {
                $this->db->trans_rollback();
                // Clean error messages for JSON response
                $clean_errors = array_map(function($error) {
                    return strip_tags(str_replace(['<br>', '<br/>', '<br />'], ' | ', $error));
                }, array_slice($errors, 0, 10));

                return [
                    'success' => false,
                    'error' => 'Staff creation failed. See details below.',
                    'detailed_errors' => $clean_errors,
                    'error_count' => count($errors)
                ];
            }

        } catch (Exception $e) {
            log_message('error', 'Staff creation processing error: ' . $e->getMessage());
            return ['success' => false, 'error' => _l('processing_error') . ': ' . $e->getMessage()];
        }

        // Resolve manager information for display
        $created_staff = $this->resolve_manager_info_for_display($created_staff);

        return ['success' => true, 'imported' => $imported, 'created_staff' => $created_staff];
    }

    private function process_reporting_manager_updates_only($confirmed_data_json)
    {
        try {
            $confirmed_data = json_decode($confirmed_data_json, true);

            if (!$confirmed_data || !isset($confirmed_data['headers']) || !isset($confirmed_data['data'])) {
                return ['success' => false, 'error' => _l('invalid_data_format')];
            }

            $headers = $confirmed_data['headers'];
            $data = $confirmed_data['data'];
            $updated = 0;
            $errors = [];

            // Start transaction for data consistency
            $this->db->trans_start();

            foreach ($data as $row_index => $row) {
                // Extract reporting manager from the original CSV data
                $reporting_manager = $this->extract_reporting_manager_from_row($row, $headers);

                if (!empty($reporting_manager)) {
                    try {
                        // Find the staff member by emp_code (from the same row)
                        $emp_code = $this->extract_emp_code_from_row($row, $headers);

                        if (!empty($emp_code)) {
                            // Get staff ID by emp_code
                            $staff_id = $this->get_staff_id_by_empcode_only($emp_code);

                            if ($staff_id) {
                                // Get manager ID
                                $manager_id = $this->get_staff_id_by_name_or_empcode($reporting_manager);

                                if ($manager_id) {
                                    // Update the reporting manager
                                    $this->db->where('staffid', $staff_id);
                                    $this->db->update(db_prefix() . 'staff', ['reporting_manager' => $manager_id]);
                                    $updated++;
                                    log_message('info', 'Updated reporting manager for staff ID ' . $staff_id . ' (emp_code: ' . $emp_code . ') to ' . $manager_id);
                                } else {
                                    $errors[] = "Row " . ($row_index + 1) . ": Reporting manager '{$reporting_manager}' not found";
                                }
                            } else {
                                $errors[] = "Row " . ($row_index + 1) . ": Staff member with emp_code '{$emp_code}' not found";
                            }
                        } else {
                            $errors[] = "Row " . ($row_index + 1) . ": No emp_code found for reporting manager update";
                        }
                    } catch (Exception $e) {
                        $errors[] = "Row " . ($row_index + 1) . ": " . $e->getMessage();
                        log_message('error', 'Reporting manager update error: ' . $e->getMessage());
                    }
                }
                // Skip rows without reporting manager (they don't need updates)
            }

            // Complete transaction
            if (empty($errors)) {
                $this->db->trans_complete();
                if ($this->db->trans_status() === FALSE) {
                    return ['success' => false, 'error' => _l('transaction_failed')];
                }
            } else {
                $this->db->trans_rollback();
                return ['success' => false, 'error' => implode('<br>', array_slice($errors, 0, 10))]; // Show first 10 errors
            }

        } catch (Exception $e) {
            log_message('error', 'Reporting manager update processing error: ' . $e->getMessage());
            return ['success' => false, 'error' => _l('processing_error') . ': ' . $e->getMessage()];
        }

        return ['success' => true, 'updated' => $updated];
    }

    private function extract_reporting_manager_from_row($row, $headers)
    {
        $column_map = [];
        foreach ($headers as $index => $column_name) {
            $column_map[$this->normalize_csv_column_name($column_name)] = $index;
        }

        $reporting_manager = isset($column_map['reporting_manager']) && isset($row[$column_map['reporting_manager']]) ? trim($row[$column_map['reporting_manager']]) : '';
        return $reporting_manager;
    }

    private function extract_emp_code_from_row($row, $headers)
    {
        $column_map = [];
        foreach ($headers as $index => $column_name) {
            $column_map[$this->normalize_csv_column_name($column_name)] = $index;
        }

        $emp_code = isset($column_map['emp_code']) && isset($row[$column_map['emp_code']]) ? trim($row[$column_map['emp_code']]) : '';
        return $emp_code;
    }

    private function get_staff_id_by_empcode_only($emp_code)
    {
        $this->db->select('cfv.relid as staffid');
        $this->db->from(db_prefix() . 'customfields cf');
        $this->db->join(db_prefix() . 'customfieldsvalues cfv', 'cf.id = cfv.fieldid');
        $this->db->where('cf.slug', 'staff_emp_code');
        $this->db->where('cfv.value', $emp_code);
        $this->db->order_by('cfv.relid', 'DESC'); // Take latest if multiple
        $this->db->limit(1);
        $result = $this->db->get()->row();
        return $result ? $result->staffid : null;
    }

    private function process_confirmed_data($confirmed_data_json)
    {
        try {
            $confirmed_data = json_decode($confirmed_data_json, true);

            if (!$confirmed_data || !isset($confirmed_data['headers']) || !isset($confirmed_data['data'])) {
                return ['success' => false, 'error' => _l('invalid_data_format')];
            }

            // Load required models
            $this->load->model('departments_model');
            $this->load->model('divisions_model');

            $headers = $confirmed_data['headers'];
            $data = $confirmed_data['data'];
            $errors = [];
            $imported = 0;
            $staff_to_email = []; // Track which staff need welcome emails
            $reporting_manager_updates = []; // Track staff that need reporting manager updates

            // Start transaction for data consistency
            $this->db->trans_start();

            foreach ($data as $row_index => $row) {
                $staff_data = $this->map_csv_row_to_staff_data($row, $headers);

                if ($staff_data) {
                    try {
                        // Extract emp_code, reporting_manager, departments, and send_welcome_email flags before removing them from staff_data
                        $emp_code = isset($staff_data['emp_code']) ? $staff_data['emp_code'] : '';
                        $reporting_manager = isset($staff_data['reporting_manager']) ? $staff_data['reporting_manager'] : '';
                        $departments = isset($staff_data['departments']) ? $staff_data['departments'] : [];
                        $send_welcome_email = isset($staff_data['send_welcome_email']) ? $staff_data['send_welcome_email'] : false;

                        // Temporarily remove fields that need special handling
                        unset($staff_data['emp_code']); // Remove from data array as it's stored as custom field
                        unset($staff_data['reporting_manager']); // Will be set after all staff are created
                        unset($staff_data['departments']); // Will be handled by Staff_model->add()
                        unset($staff_data['send_welcome_email']); // Remove from data array as it's not a DB field

                        // Add departments back to staff_data for Staff_model->add() to handle
                        if (!empty($departments)) {
                            $staff_data['departments'] = $departments;
                        }

                        $result = $this->staff_model->add($staff_data);
                        if ($result) {
                            // Save emp_code as custom field
                            if (!empty($emp_code)) {
                                $this->save_staff_emp_code($result, $emp_code);
                            }

                            // Track reporting manager updates if specified
                            if (!empty($reporting_manager)) {
                                $reporting_manager_updates[] = [
                                    'staff_id' => $result,
                                    'manager_identifier' => $reporting_manager,
                                    'row_number' => $row_index + 1
                                ];
                            }

                            $imported++;

                            // Track staff who need welcome emails
                            if ($send_welcome_email) {
                                $staff_to_email[] = $result;
                            }
                        } else {
                            $errors[] = "Row " . ($row_index + 1) . ": " . _l('failed_to_create_staff');
                        }
                    } catch (Exception $e) {
                        $errors[] = "Row " . ($row_index + 1) . ": " . $e->getMessage();
                        log_message('error', 'Staff creation error: ' . $e->getMessage());
                    }
                } else {
                    $errors[] = "Row " . ($row_index + 1) . ": " . _l('invalid_data_format');
                }
            }

            // Now update reporting managers for all staff that had them specified
            // Since all staff are now created, we can safely look up managers
            foreach ($reporting_manager_updates as $update) {
                $manager_id = $this->get_staff_id_by_name_or_empcode($update['manager_identifier']);
                if ($manager_id) {
                    // Update the reporting manager
                    $this->db->where('staffid', $update['staff_id']);
                    $this->db->update(db_prefix() . 'staff', ['reporting_manager' => $manager_id]);
                    log_message('info', 'Updated reporting manager for staff ID ' . $update['staff_id'] . ' to ' . $manager_id);
                } else {
                    // Manager not found - log as error since all staff should be created by now
                    log_message('error', 'Reporting manager not found for staff ID ' . $update['staff_id'] . ': ' . $update['manager_identifier'] . ' - All staff have been created, this should not happen');
                }
            }

            // Complete transaction
            if (empty($errors)) {
                $this->db->trans_complete();
                if ($this->db->trans_status() === FALSE) {
                    return ['success' => false, 'error' => _l('transaction_failed')];
                }

                // Send welcome emails to staff who requested them
                if (!empty($staff_to_email)) {
                    $this->send_bulk_welcome_emails($staff_to_email);
                }
            } else {
                $this->db->trans_rollback();
                return ['success' => false, 'error' => implode('<br>', array_slice($errors, 0, 10))]; // Show first 10 errors
            }

        } catch (Exception $e) {
            log_message('error', 'Confirmed data processing error: ' . $e->getMessage());
            return ['success' => false, 'error' => _l('processing_error') . ': ' . $e->getMessage()];
        }

        return ['success' => true, 'imported' => $imported];
    }

    private function send_bulk_welcome_emails($staff_ids)
    {
        if (empty($staff_ids)) {
            return;
        }

        // Load email library
        $this->load->library('email');

        foreach ($staff_ids as $staff_id) {
            $staff = $this->staff_model->get($staff_id);
            if ($staff && !empty($staff->email)) {
                try {
                    // Send welcome email
                    $this->email->clear();
                    $this->email->from(get_option('smtp_email'), get_option('companyname'));
                    $this->email->to($staff->email);
                    $this->email->subject('Welcome to ' . get_option('companyname'));

                    $message = "Dear {$staff->firstname} {$staff->lastname},\n\n";
                    $message .= "Welcome to " . get_option('companyname') . "!\n\n";
                    $message .= "Your account has been created successfully.\n";
                    $message .= "Email: {$staff->email}\n";
                    $message .= "Password: (Please contact your administrator to set your password)\n\n";
                    $message .= "Please login to your account and change your password.\n\n";
                    $message .= "Best regards,\n";
                    $message .= get_option('companyname') . " Team";

                    $this->email->message($message);
                    $this->email->send();

                    log_message('info', 'Welcome email sent to staff member: ' . $staff->email);
                } catch (Exception $e) {
                    log_message('error', 'Failed to send welcome email to ' . $staff->email . ': ' . $e->getMessage());
                }
            }
        }
    }

    private function get_upload_error_message($error_code)
    {
        switch ($error_code) {
            case UPLOAD_ERR_INI_SIZE:
                return _l('file_too_large');
            case UPLOAD_ERR_FORM_SIZE:
                return _l('file_too_large');
            case UPLOAD_ERR_PARTIAL:
                return _l('file_upload_partial');
            case UPLOAD_ERR_NO_FILE:
                return _l('no_file_uploaded');
            case UPLOAD_ERR_NO_TMP_DIR:
                return _l('no_temp_directory');
            case UPLOAD_ERR_CANT_WRITE:
                return _l('cannot_write_file');
            case UPLOAD_ERR_EXTENSION:
                return _l('upload_blocked_extension');
            default:
                return _l('file_upload_error');
        }
    }

    /* Validate database references for bulk upload */
    public function validate_bulk_upload_references()
    {
        if (!staff_can('create', 'staff')) {
            ajax_access_denied('staff');
        }

        if ($this->input->post()) {
            $validation_data_json = $this->input->post('validation_data');

            if (!$validation_data_json) {
                echo json_encode(['errors' => ['Invalid validation data']]);
                return;
            }

            try {
                $validation_data = json_decode($validation_data_json, true);

                if (!$validation_data || !isset($validation_data['headers']) || !isset($validation_data['data'])) {
                    echo json_encode(['errors' => ['Invalid validation data format']]);
                    return;
                }

                $headers = $validation_data['headers'];
                $data = $validation_data['data'];
                $errors = [];

                // Validate each row for database references
                foreach ($data as $row_index => $row) {
                    $row_errors = $this->validate_row_references($row, $headers, $row_index + 2);
                    if (!empty($row_errors)) {
                        $errors = array_merge($errors, $row_errors);
                    }
                }

                echo json_encode(['errors' => $errors]);

            } catch (Exception $e) {
                log_message('error', 'Bulk upload reference validation error: ' . $e->getMessage());
                echo json_encode(['errors' => ['Validation failed: ' . $e->getMessage()]]);
            }
        }
    }

    private function save_staff_emp_code($staff_id, $emp_code)
    {
        // Get the custom field ID for staff_emp_code
        $this->db->select('id');
        $this->db->from(db_prefix() . 'customfields');
        $this->db->where('slug', 'staff_emp_code');
        $this->db->where('fieldto', 'staff');
        $custom_field = $this->db->get()->row();

        if ($custom_field) {
            // Check if value already exists for this staff member
            $this->db->select('id');
            $this->db->from(db_prefix() . 'customfieldsvalues');
            $this->db->where('fieldid', $custom_field->id);
            $this->db->where('relid', $staff_id);
            $existing_value = $this->db->get()->row();

            if ($existing_value) {
                // Update existing value
                $this->db->where('id', $existing_value->id);
                $this->db->update(db_prefix() . 'customfieldsvalues', ['value' => $emp_code]);
            } else {
                // Insert new value
                $this->db->insert(db_prefix() . 'customfieldsvalues', [
                    'fieldid' => $custom_field->id,
                    'relid' => $staff_id,
                    'fieldto' => 'staff',
                    'value' => $emp_code
                ]);
            }
        }
    }

    private function validate_row_references($row, $headers, $row_number)
    {
        $errors = [];

        // Map columns
        $column_map = [];
        foreach ($headers as $index => $column_name) {
            $column_map[$this->normalize_csv_column_name($column_name)] = $index;
        }

        // Check division
        $division = isset($column_map['division']) && isset($row[$column_map['division']]) ? trim($row[$column_map['division']]) : '';
        if (!empty($division)) {
            $div_id = $this->get_division_id_by_name($division);
            if (!$div_id) {
                $errors[] = "Row {$row_number}: Division '{$division}' does not exist in the system.";
            }
        }

        // Check department
        $department = isset($column_map['department']) && isset($row[$column_map['department']]) ? trim($row[$column_map['department']]) : '';
        if (!empty($department)) {
            $dept_id = $this->get_department_id_by_name($department);
            if (!$dept_id) {
                $errors[] = "Row {$row_number}: Department '{$department}' does not exist in the system.";
            }
        }

        // Check sub department
        $sub_department = isset($column_map['sub_department']) && isset($row[$column_map['sub_department']]) ? trim($row[$column_map['sub_department']]) : '';
        if (!empty($sub_department)) {
            $sub_dept_id = $this->get_department_id_by_name($sub_department);
            if (!$sub_dept_id) {
                $errors[] = "Row {$row_number}: Sub Department '{$sub_department}' does not exist in the system.";
            }
        }

        // Note: Reporting manager validation is skipped during bulk upload preview
        // It will be validated during actual processing after all staff are created
        // This allows managers and their subordinates to be created in the same batch

        return $errors;
    }

    /* Process bulk import batch via AJAX */
    public function process_bulk_import_batch()
    {
        if (!staff_can('create', 'staff')) {
            ajax_access_denied('staff');
        }

        if ($this->input->post()) {
            $import_data_json = $this->input->post('import_data');

            if (!$import_data_json) {
                echo json_encode(['results' => []]);
                return;
            }

            try {
                $import_data = json_decode($import_data_json, true);

                if (!$import_data || !isset($import_data['headers']) || !isset($import_data['data'])) {
                    echo json_encode(['results' => []]);
                    return;
                }

                $headers = $import_data['headers'];
                $data = $import_data['data'];
                $results = [];

                // Load required models
                $this->load->model('departments_model');
                $this->load->model('divisions_model');

                // Process each row in the batch
                foreach ($data as $row_index => $row) {
                    $result = $this->process_single_row($row, $headers, $row_index + 1);
                    $results[] = $result;
                }

                echo json_encode(['results' => $results]);

            } catch (Exception $e) {
                log_message('error', 'Bulk import batch processing error: ' . $e->getMessage());
                echo json_encode(['results' => []]);
            }
        }
    }

    /* Complete bulk import step 1 and prepare for step 2 */
    public function complete_bulk_import_step1()
    {
        // Set proper JSON headers
        header('Content-Type: application/json');

        if (!staff_can('create', 'staff')) {
            echo json_encode(['success' => false, 'error' => 'Access denied']);
            return;
        }

        if (!$this->input->post()) {
            echo json_encode(['success' => false, 'error' => 'Invalid request method']);
            return;
        }

        $confirmed_data_json = $this->input->post('confirmed_data');

        if (!$confirmed_data_json) {
            echo json_encode(['success' => false, 'error' => 'No data provided']);
            return;
        }

        try {
            // Process staff creation only (step 1)
            $result = $this->process_staff_creation_only($confirmed_data_json);

            if ($result['success']) {
                // Store session data for step 2
                $this->session->set_userdata('bulk_upload_pending_managers', $confirmed_data_json);
                $this->session->set_userdata('bulk_upload_created_staff', $result['created_staff']);

                echo json_encode([
                    'success' => true,
                    'message' => 'Successfully created ' . $result['imported'] . ' staff members. Now proceed to update reporting managers.',
                    'imported' => $result['imported'],
                    'created_staff' => $result['created_staff']
                ]);
            } else {
                // Ensure error response is properly formatted
                $error_response = [
                    'success' => false,
                    'error' => isset($result['error']) ? $result['error'] : 'Unknown error occurred'
                ];

                // Add detailed errors if available
                if (isset($result['detailed_errors'])) {
                    $error_response['detailed_errors'] = $result['detailed_errors'];
                }
                if (isset($result['error_count'])) {
                    $error_response['error_count'] = $result['error_count'];
                }

                echo json_encode($error_response);
            }

        } catch (Exception $e) {
            log_message('error', 'Bulk import step 1 completion error: ' . $e->getMessage());
            echo json_encode([
                'success' => false,
                'error' => 'System error occurred during processing',
                'debug' => $e->getMessage() // Remove this in production
            ]);
        }
    }

    private function process_single_row($row, $headers, $row_number)
    {
        try {
            $staff_data = $this->map_csv_row_to_staff_data($row, $headers);

            if (!$staff_data) {
                return ['success' => false, 'message' => 'Invalid data format'];
            }

            // Extract fields that need special handling
            $emp_code = isset($staff_data['emp_code']) ? $staff_data['emp_code'] : '';
            $departments = isset($staff_data['departments']) ? $staff_data['departments'] : [];
            $send_welcome_email = isset($staff_data['send_welcome_email']) ? $staff_data['send_welcome_email'] : false;

            // Filter data to only include fields expected by Staff_model->add()
            $allowed_fields = [
                'firstname', 'lastname', 'email', 'password', 'role', 'active',
                'phonenumber', 'divisionid', 'reporting_manager'
            ];

            $filtered_data = [];
            foreach ($allowed_fields as $field) {
                if (isset($staff_data[$field])) {
                    $filtered_data[$field] = $staff_data[$field];
                }
            }

            // Add departments back for Staff_model->add() to handle
            if (!empty($departments)) {
                $filtered_data['departments'] = $departments;
            }

            // Create staff member
            $result = $this->staff_model->add($filtered_data);

            if ($result) {
                // Save emp_code as custom field
                if (!empty($emp_code)) {
                    $this->save_staff_emp_code($result, $emp_code);
                }

                // Send welcome email if requested
                if ($send_welcome_email) {
                    $this->send_single_welcome_email($result);
                }

                return ['success' => true, 'message' => 'Staff member created successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to create staff member'];
            }

        } catch (Exception $e) {
            log_message('error', 'Error processing single row: ' . $e->getMessage());
            return ['success' => false, 'message' => 'System error: ' . $e->getMessage()];
        }
    }

    private function send_single_welcome_email($staff_id)
    {
        $staff = $this->staff_model->get($staff_id);
        if ($staff && !empty($staff->email)) {
            try {
                // Load email library
                $this->load->library('email');

                // Send welcome email
                $this->email->clear();
                $this->email->from(get_option('smtp_email'), get_option('companyname'));
                $this->email->to($staff->email);
                $this->email->subject('Welcome to ' . get_option('companyname'));

                $message = "Dear {$staff->firstname} {$staff->lastname},\n\n";
                $message .= "Welcome to " . get_option('companyname') . "!\n\n";
                $message .= "Your account has been created successfully.\n";
                $message .= "Email: {$staff->email}\n";
                $message .= "Password: (Please contact your administrator to set your password)\n\n";
                $message .= "Please login to your account and change your password.\n\n";
                $message .= "Best regards,\n";
                $message .= get_option('companyname') . " Team";

                $this->email->message($message);
                $this->email->send();

                log_message('info', 'Welcome email sent to staff member: ' . $staff->email);
            } catch (Exception $e) {
                log_message('error', 'Failed to send welcome email to ' . $staff->email . ': ' . $e->getMessage());
            }
        }
    }

    /* Clear bulk upload session data */
    public function clear_bulk_upload_session()
    {
        if (!staff_can('create', 'staff')) {
            ajax_access_denied('staff');
        }

        // Clear all bulk upload related session data
        $this->session->unset_userdata('bulk_upload_pending_managers');
        $this->session->unset_userdata('bulk_upload_created_staff');
        $this->session->unset_userdata('bulk_upload_complete');

        echo json_encode(['success' => true]);
    }

    private function resolve_manager_info_for_display($created_staff)
    {
        foreach ($created_staff as &$staff) {
            if (!empty($staff['reporting_manager'])) {
                // Try to resolve manager information
                $manager_id = $this->get_staff_id_by_name_or_empcode($staff['reporting_manager']);
                if ($manager_id) {
                    $manager = $this->staff_model->get($manager_id);
                    if ($manager) {
                        // Get manager's emp code
                        $manager_emp_code = '';
                        $this->db->select('cfv.value as emp_code');
                        $this->db->from(db_prefix() . 'customfields cf');
                        $this->db->join(db_prefix() . 'customfieldsvalues cfv', 'cf.id = cfv.fieldid');
                        $this->db->where('cf.slug', 'staff_emp_code');
                        $this->db->where('cfv.relid', $manager_id);
                        $emp_result = $this->db->get()->row();
                        if ($emp_result) {
                            $manager_emp_code = $emp_result->emp_code;
                        }

                        $staff['manager_info'] = [
                            'staffid' => $manager->staffid,
                            'name' => $manager->firstname . ' ' . $manager->lastname,
                            'emp_code' => $manager_emp_code
                        ];
                    }
                }
            }
        }
        return $created_staff;
    }
}
