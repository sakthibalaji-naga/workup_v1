<?php

use GuzzleHttp\Client;

defined('BASEPATH') or exit('No direct script access allowed');

define('SMS_TRIGGER_INVOICE_OVERDUE', 'invoice_overdue_notice');
define('SMS_TRIGGER_INVOICE_DUE', 'invoice_due_notice');
define('SMS_TRIGGER_PAYMENT_RECORDED', 'invoice_payment_recorded');
define('SMS_TRIGGER_ESTIMATE_EXP_REMINDER', 'estimate_expiration_reminder');
define('SMS_TRIGGER_PROPOSAL_EXP_REMINDER', 'proposal_expiration_reminder');
define('SMS_TRIGGER_PROPOSAL_NEW_COMMENT_TO_CUSTOMER', 'proposal_new_comment_to_customer');
define('SMS_TRIGGER_PROPOSAL_NEW_COMMENT_TO_STAFF', 'proposal_new_comment_to_staff');
define('SMS_TRIGGER_CONTRACT_EXP_REMINDER', 'contract_expiration_reminder');
define('SMS_TRIGGER_CONTRACT_SIGN_REMINDER', 'contract_sign_reminder_to_customer');
define('SMS_TRIGGER_STAFF_REMINDER', 'staff_reminder');
define('SMS_TRIGGER_STAFF_PASSWORD_RESET', 'staff_password_reset');

define('SMS_TRIGGER_CONTRACT_NEW_COMMENT_TO_STAFF', 'contract_new_comment_to_staff');
define('SMS_TRIGGER_CONTRACT_NEW_COMMENT_TO_CUSTOMER', 'contract_new_comment_to_customer');
define('SMS_TRIGGER_NEW_TICKET_CREATED', 'new_ticket_created');
define('SMS_TRIGGER_HANDLER_ASSIGNED', 'handler_assigned');
define('SMS_TRIGGER_TICKET_REASSIGNMENT_REQUEST', 'ticket_reassignment_request');
define('SMS_TRIGGER_TICKET_WAITING_FOR_CLOSE', 'ticket_waiting_for_close');
define('SMS_TRIGGER_TICKET_REOPEN_REQUEST', 'ticket_reopen_request');

class App_sms
{
    private static $gateways;

    protected $client;

    private $triggers = [];

    protected $ci;

    public static $trigger_being_sent;

    public $test_mode = false;

    public function __construct()
    {
        $this->ci = &get_instance();

        if (function_exists('load_custom_lang_file')) {
            $language = get_option('active_language');

            if (function_exists('is_staff_logged_in') && is_staff_logged_in()
                && function_exists('get_staff_default_language')) {
                $staffLanguage = get_staff_default_language();
                if (!empty($staffLanguage) && file_exists(APPPATH . 'language/' . $staffLanguage . '/custom_lang.php')) {
                    $language = $staffLanguage;
                }
            }

            if (empty($language)) {
                $language = 'english';
            }

            if (!file_exists(APPPATH . 'language/' . $language . '/custom_lang.php')
                && file_exists(APPPATH . 'language/english/custom_lang.php')) {
                $language = 'english';
            }

            load_custom_lang_file($language);
        }

        $this->client = new Client(
            [ 'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept'       => 'application/json',
                ],
                'verify'               => false,
                CURLOPT_RETURNTRANSFER => true,
            ]
        );
        $this->set_default_triggers();
    }

    public function add_gateway($id, $data = [])
    {
        if (!$this->is_initialized($id) || $this->is_options_page()) {
            foreach ($data['options'] as $option) {
                add_option($this->option_name($id, $option['name']), (isset($option['default_value']) ? $option['default_value'] : ''));
            }

            add_option($this->option_name($id, 'active'), 0);
            add_option($this->option_name($id, 'initialized'), 1);
        }

        $data['id'] = $id;

        self::$gateways[$id] = $data;
    }

    public function get_option($id, $option)
    {
        return get_option($this->option_name($id, $option));
    }

    public function get_gateway($id)
    {
        $gateway = isset(self::$gateways[$id]) ? self::$gateways[$id] : null;

        return $gateway;
    }

    public function set_test_mode($value)
    {
        $this->test_mode = $value;

        return $this;
    }

    public function get_gateways()
    {
        return hooks()->apply_filters('get_sms_gateways', self::$gateways);
    }

    public function get_trigger_value($trigger)
    {
        $oc_name = 'sms-trigger-' . $trigger . '-value';
        $message = $this->ci->app_object_cache->get($oc_name);
        if (!$message) {
            $message = get_option($this->trigger_option_name($trigger));
            $this->ci->app_object_cache->add($oc_name, $message);
        }

        return $message;
    }

    public function add_trigger($trigger)
    {
        $this->triggers = array_merge($this->triggers, $trigger);
    }

    public function get_available_triggers()
    {
        $triggers = hooks()->apply_filters('sms_gateway_available_triggers', $this->triggers);

        foreach ($triggers as $trigger_id => $triger) {
            if ($this->is_options_page()) {
                add_option($this->trigger_option_name($trigger_id), '', 0);
                add_option($this->trigger_option_name($trigger_id) . '_active', '0', 0);
            }
            $triggers[$trigger_id]['value'] = $this->get_trigger_value($trigger_id);
        }

        return $triggers;
    }

    public function trigger($trigger, $phone, $merge_fields = [])
    {
        if (empty($phone)) {
            return false;
        }

        $gateway = $this->get_active_gateway();

        if ($gateway !== false) {
            $className = 'sms_' . $gateway['id'];
            if ($this->is_trigger_active($trigger)) {
                $message = $this->parse_merge_fields(
                    $merge_fields,
                    $this->get_trigger_value($trigger)
                );

                $message = clear_textarea_breaks($message);

                static::$trigger_being_sent = $trigger;

                // Log SMS to database (all triggers)
                $log_id = $this->logSms($gateway, $phone, $message, $merge_fields, $trigger);

                $retval = $this->ci->{$className}->send($phone, $message, $trigger);

                // Update the log with success status
                if ($retval) {
                    $this->updateSmsLogStatus($log_id, true);
                } else {
                    $this->updateSmsLogStatus($log_id, false, $this->ci->app_sms->get_error());
                }

                hooks()->do_action('sms_trigger_triggered', ['message' => $message, 'trigger' => $trigger, 'phone' => $phone]);

                static::$trigger_being_sent = null;

                return $retval;
            }
        }

        return false;
    }

    /**
     * Parse sms gateway merge fields
     * We will use the email templates merge fields function because they are the same
     * @param  array $merge_fields merge fields
     * @param  string $message      the message to bind the merge fields
     * @return string
     */
    public function parse_merge_fields($merge_fields, $message)
    {
        $template           = new stdClass();
        $template->message  = $message;
        $template->subject  = '';
        $template->fromname = '';

        return parse_email_template_merge_fields($template, $merge_fields)->message;
    }

    /**
     * Log SMS to database
     * @param array $gateway Active gateway info
     * @param string $phone Phone number
     * @param string $message SMS content
     * @param array $merge_fields Merge fields used
     * @param string $trigger SMS trigger type
     * @return int Log ID
     */
    protected function logSms($gateway, $phone, $message, $merge_fields, $trigger)
    {
        // Get related data based on trigger and merge fields
        $related_data = $this->getRelatedData($trigger, $merge_fields);

        // Handle legacy password reset logging
        if ($trigger === SMS_TRIGGER_STAFF_PASSWORD_RESET) {
            $otp_code = isset($merge_fields['otp_code']) ? $merge_fields['otp_code'] : '';
            $staff_id = isset($merge_fields['staff_id']) ? $merge_fields['staff_id'] : null;

            if ($staff_id) {
                // Also insert into legacy password reset table for backwards compatibility
                $this->ci->db->insert('tbl_sms_password_reset_logs', [
                    'staffid' => $staff_id,
                    'phone_number' => $phone,
                    'otp_code' => $otp_code,
                    'sms_gateway' => $gateway['id'],
                    'trigger_type' => $trigger,
                    'message_content' => $message,
                    'status' => 'queued',
                    'ip_address' => $this->ci->input->ip_address(),
                    'user_agent' => $this->ci->input->user_agent(),
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }

        $this->ci->db->insert('tbl_sms_logs', [
            'trigger_type' => $trigger,
            'phone_number' => $phone,
            'sms_gateway' => $gateway['id'],
            'message_content' => $message,
            'merge_fields' => json_encode($merge_fields),
            'status' => 'queued',
            'queue_at' => date('Y-m-d H:i:s'),
            'ip_address' => $this->ci->input->ip_address(),
            'user_agent' => $this->ci->input->user_agent(),
            'staff_id' => $related_data['staff_id'],
            'client_id' => $related_data['client_id'],
            'contact_id' => $related_data['contact_id'],
            'related_record_id' => $related_data['record_id'],
            'related_record_type' => $related_data['record_type'],
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->ci->db->insert_id();
    }

    /**
     * Update SMS log status
     * @param int $log_id Log ID
     * @param bool $success Whether SMS was sent successfully
     * @param string $error_message Error message if failed
     */
    protected function updateSmsLogStatus($log_id, $success, $error_message = null)
    {
        $this->ci->db->where('id', $log_id)
                     ->update('tbl_sms_logs', [
                         'status' => $success ? 'sent' : 'failed',
                         'sent_at' => $success ? date('Y-m-d H:i:s') : null,
                         'error_message' => $success ? null : $error_message,
                         'updated_at' => date('Y-m-d H:i:s'),
                     ]);

        // Handle legacy password reset logging
        $log = $this->ci->db->select('trigger_type, phone_number')->where('id', $log_id)->get('tbl_sms_logs')->row();
        if ($log && $log->trigger_type === SMS_TRIGGER_STAFF_PASSWORD_RESET) {
            $this->updatePasswordResetLogStatus($log->phone_number, $success, $error_message);
        }
    }

    /**
     * Legacy method for backwards compatibility with password reset logs
     * @param string $phone Phone number
     * @param bool $success Whether SMS was sent successfully
     * @param string $error_message Error message if failed
     */
    protected function updatePasswordResetLogStatus($phone, $success, $error_message = null)
    {
        $this->ci->db->where('phone_number', $phone)
                     ->where('status', 'queued')
                     ->order_by('created_at', 'desc')
                     ->limit(1);

        $log = $this->ci->db->get('tbl_sms_password_reset_logs')->row();

        if ($log) {
            $this->ci->db->where('id', $log->id)
                         ->update('tbl_sms_password_reset_logs', [
                             'status' => $success ? 'sent' : 'failed',
                             'sent_at' => $success ? date('Y-m-d H:i:s') : null,
                             'error_message' => $success ? null : $error_message,
                             'attempts_count' => $success ? $log->attempts_count : ($log->attempts_count + 1),
                             'updated_at' => date('Y-m-d H:i:s'),
                         ]);
        }
    }

    /**
     * Extract related data from merge fields and trigger type
     * @param string $trigger SMS trigger type
     * @param array $merge_fields Merge fields
     * @return array Related data array
     */
    protected function getRelatedData($trigger, $merge_fields)
    {
        $data = [
            'staff_id' => null,
            'client_id' => null,
            'contact_id' => null,
            'record_id' => null,
            'record_type' => null,
        ];

        // Extract IDs from merge fields based on trigger type
        switch ($trigger) {
            case SMS_TRIGGER_NEW_TICKET_CREATED:
            case SMS_TRIGGER_HANDLER_ASSIGNED:
            case SMS_TRIGGER_TICKET_REASSIGNMENT_REQUEST:
            case SMS_TRIGGER_TICKET_WAITING_FOR_CLOSE:
            case SMS_TRIGGER_TICKET_REOPEN_REQUEST:
                $ticket_id = isset($merge_fields['{ticket_id}']) ? $merge_fields['{ticket_id}'] : '';
                if ($ticket_id && is_numeric($ticket_id)) {
                    $data['record_id'] = (int)$ticket_id;
                    $data['record_type'] = 'ticket';
                }
                break;

            case SMS_TRIGGER_STAFF_PASSWORD_RESET:
            case SMS_TRIGGER_STAFF_REMINDER:
                // Staff-related SMS
                if (isset($merge_fields['staff_id'])) {
                    $data['staff_id'] = (int)$merge_fields['staff_id'];
                } elseif (isset($merge_fields['{staff_firstname}'])) {
                    $staff_id = $this->findStaffFromMergeFields($merge_fields);
                    if ($staff_id) {
                        $data['staff_id'] = (int)$staff_id;
                    }
                }
                break;

            // Add more cases for other trigger types as needed
        }

        return $data;
    }

    /**
     * Find staff ID from merge fields
     * @param array $merge_fields Merge fields
     * @return int|null Staff ID
     */
    protected function findStaffFromMergeFields($merge_fields)
    {
        // Try to find staff by phone number or other identifiable fields
        if (isset($merge_fields['{staff_phone}'])) {
            $this->ci->db->select('staffid');
            $this->ci->db->where('phonenumber', $merge_fields['{staff_phone}']);
            $staff = $this->ci->db->get('tblstaff')->row();
            if ($staff) {
                return $staff->staffid;
            }
        }

        // If not found by phone, could add more lookup methods here
        return null;
    }

    public function option_name($id, $option)
    {
        return 'sms_' . $id . '_' . $option;
    }

    public function trigger_option_name($trigger)
    {
        return 'sms_trigger_' . $trigger;
    }

    public function is_any_trigger_active()
    {
        $triggers = $this->get_available_triggers();
        $active   = false;
        foreach ($triggers as $trigger_id => $trigger_opts) {
            if ($this->_is_trigger_message_empty($this->get_trigger_value($trigger_id))) {
                $active = true;

                break;
            }
        }

        return $active;
    }

    protected function set_error($error, $log_message = true)
    {
        $GLOBALS['sms_error'] = $error;

        if ($log_message) {
            log_activity('Failed to send SMS via ' . get_class($this) . ': ' . $error);
        }

        return $this;
    }

    public function get_error()
    {
        return isset($GLOBALS['sms_error']) ? $GLOBALS['sms_error'] : null;
    }

    private function _is_trigger_message_empty($message)
    {
        if (trim($message) === '') {
            return false;
        }

        return true;
    }

    public function is_trigger_active($trigger)
    {
        if ($trigger != '') {
            // Check if trigger is enabled via the active option
            if (get_option($this->trigger_option_name($trigger) . '_active') != '1') {
                return false;
            }
            // Check if message is not empty
            if (!$this->_is_trigger_message_empty($this->get_trigger_value($trigger))) {
                return false;
            }
        } else {
            return $this->is_any_trigger_active();
        }

        return true;
    }

    public function get_active_gateway()
    {
        $active = false;

        foreach (self::$gateways as $gateway) {
            if ($this->get_option($gateway['id'], 'active') == '1') {
                $active = $gateway;

                break;
            }
        }

        return $active;
    }

    /**
     * Check if is settings page in admin area
     * @return boolean
     */
    private function is_options_page()
    {
        return $this->ci->input->get('group') == 'sms' && $this->ci->uri->segment(2) == 'settings';
    }

    /**
     * Check if sms gateway is initialized and options are added into database
     * @return boolean
     */
    private function is_initialized($id)
    {
        return $this->get_option($id, 'initialized') == '' ? false : true;
    }

    /**
     * Log success message
     *
     * @param  string $number
     * @param  string $message
     *
     * @return void
     */
    protected function logSuccess($number, $message)
    {
        return log_activity('SMS sent to ' . $number . ', Message: ' . $message);
    }

    private function set_default_triggers()
    {
        $customer_merge_fields = [
            '{contact_firstname}',
            '{contact_lastname}',
            '{client_company}',
            '{client_vat_number}',
            '{client_id}',
        ];

        $invoice_merge_fields = [
            '{invoice_link}',
            '{invoice_number}',
            '{invoice_duedate}',
            '{invoice_date}',
            '{invoice_status}',
            '{invoice_subtotal}',
            '{invoice_total}',
            '{invoice_amount_due}',
            '{invoice_short_url}',
        ];

        $proposal_merge_fields = [
            '{proposal_number}',
            '{proposal_id}',
            '{proposal_subject}',
            '{proposal_date}',
            '{proposal_open_till}',
            '{proposal_subtotal}',
            '{proposal_total}',
            '{proposal_proposal_to}',
            '{proposal_link}',
            '{proposal_short_url}',
        ];

        $contract_merge_fields = [
            '{contract_id}',
            '{contract_subject}',
            '{contract_datestart}',
            '{contract_dateend}',
            '{contract_contract_value}',
            '{contract_link}',
            '{contract_short_url}',
        ];

        $triggers = [
            SMS_TRIGGER_NEW_TICKET_CREATED => [
                'merge_fields' => [
                    '{ticket_id}',
                    '{ticket_number}',
                    '{ticket_subject}',
                    '{staff_firstname}',
                    '{staff_lastname}',
                ],
                'label' => _l('sms_trigger_new_ticket_created'),
                'info'  => _l('sms_trigger_new_ticket_created_info'),
            ],

            SMS_TRIGGER_HANDLER_ASSIGNED => [
                'merge_fields' => [
                    '{ticket_id}',
                    '{ticket_number}',
                    '{ticket_subject}',
                    '{priority}',
                    '{due_date}',
                    '{staff_firstname}',
                    '{staff_lastname}',
                    '{company_name}',
                ],
                'label' => 'Handler Assigned',
                'info'  => 'Trigger when a ticket handler is assigned to a ticket.',
            ],

            SMS_TRIGGER_TICKET_REASSIGNMENT_REQUEST => [
                'merge_fields' => [
                    '{ticket_id}',
                    '{ticket_number}',
                    '{requester_name}',
                    '{staff_firstname}',
                    '{staff_lastname}',
                    '{company_name}',
                ],
                'label' => 'Ticket Reassignment Request',
                'info'  => 'Trigger when a ticket reassignment request is created (pending approval).',
            ],

            SMS_TRIGGER_TICKET_WAITING_FOR_CLOSE => [
                'merge_fields' => [
                    '{ticket_id}',
                    '{ticket_number}',
                    '{ticket_subject}',
                    '{marked_by_name}',
                    '{staff_firstname}',
                    '{staff_lastname}',
                    '{company_name}',
                ],
                'label' => _l('sms_trigger_ticket_waiting_for_close'),
                'info'  => _l('sms_trigger_ticket_waiting_for_close_info'),
            ],

            SMS_TRIGGER_TICKET_REOPEN_REQUEST => [
                'merge_fields' => [
                    '{ticket_id}',
                    '{ticket_number}',
                    '{requester_name}',
                    '{staff_firstname}',
                    '{staff_lastname}',
                    '{company_name}',
                ],
                'label' => 'Ticket Reopen Request',
                'info'  => 'Trigger when a ticket reopen request is submitted.',
            ],







            SMS_TRIGGER_STAFF_REMINDER => [
                'merge_fields' => [
                    '{staff_firstname}',
                    '{staff_lastname}',
                    '{staff_reminder_description}',
                    '{staff_reminder_date}',
                    '{staff_reminder_relation_name}',
                    '{staff_reminder_relation_link}',
                ],
                'label' => 'Staff Reminder',
                'info'  => 'Trigger when staff is notified for a specific custom <a href="' . admin_url('misc/reminders') . '">reminder</a>.',
            ],

            SMS_TRIGGER_STAFF_PASSWORD_RESET => [
                'merge_fields' => [
                    '{staff_firstname}',
                    '{staff_lastname}',
                    '{otp_code}',
                ],
                'label' => 'Staff Password Reset',
                'info'  => 'Trigger when staff member requests a password reset.',
            ],
        ];

        $this->triggers = hooks()->apply_filters('sms_triggers', $triggers);
    }
}
