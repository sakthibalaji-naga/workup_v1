<?php

use app\services\imap\Imap;
use app\services\imap\ConnectionErrorException;
use Ddeboer\Imap\Exception\MailboxDoesNotExistException;

defined('BASEPATH') or exit('No direct script access allowed');

class Application extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('application_model');

        if (!is_admin()) {
            access_denied('Application');
        }
    }

    /* List all applications */
    public function index()
    {
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data('application');
        }
        $this->load->model('departments_model');
        $this->load->model('tickets_model');
        $data['departments'] = $this->departments_model->get();
        $data['services'] = $this->tickets_model->get_service();
        $data['title'] = _l('application');
        $this->load->view('admin/application/manage', $data);
    }

    /* Edit or add new application */
    public function application($id = '')
    {
        if ($this->input->post()) {
            $message          = '';
            $data             = $this->input->post();
            
            if (!$this->input->post('id')) {
                $id = $this->application_model->add($data);
                if ($id) {
                    $success = true;
                    $message = _l('added_successfully', _l('application'));
                }
                echo json_encode([
                    'success'              => $success,
                    'message'              => $message,
                ]);
            } else {
                $id = $data['id'];
                unset($data['id']);
                $success = $this->application_model->update($data, $id);
                if ($success) {
                    $message = _l('updated_successfully', _l('application'));
                }
                echo json_encode([
                    'success'              => $success,
                    'message'              => $message,
                ]);
            }
            die;
        }
    }

    /* Delete application from database */
    public function delete($id)
    {
        if (!$id) {
            redirect(admin_url('application'));
        }
        $response = $this->application_model->delete($id);
        if (is_array($response) && isset($response['referenced'])) {
            set_alert('warning', _l('is_referenced', _l('application_lowercase')));
        } elseif ($response == true) {
            set_alert('success', _l('deleted', _l('application')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('application_lowercase')));
        }
        redirect(admin_url('application'));
    }

    /* Toggle application active/inactive status */
    public function toggle_status($id)
    {
        // Ensure this is an AJAX request
        if (!$this->input->is_ajax_request()) {
            redirect(admin_url('application'));
        }

        if (!$id) {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid application ID'
            ]);
            die;
        }

        $application = $this->application_model->get($id);
        if (!$application) {
            echo json_encode([
                'success' => false,
                'message' => 'Application not found'
            ]);
            die;
        }

        // Check if active column exists, default to 1 (active) if not
        $current_status = isset($application->active) ? $application->active : 1;
        $new_status = $current_status ? 0 : 1; // Toggle status

        // If deactivating, check for linked services
        if ($new_status == 0) {
            $linked_services = $this->application_model->get_linked_services($id);
            if (!empty($linked_services)) {
                $active_linked_services = $this->application_model->count_active_linked_services($id);

                echo json_encode([
                    'success' => false,
                    'show_confirmation' => true,
                    'linked_services' => $linked_services,
                    'application_name' => isset($application->name) ? $application->name : '',
                    'total_linked_services' => count($linked_services),
                    'active_linked_services' => $active_linked_services,
                ]);
                die;
            }
        }

        // Perform the toggle
        $success = $this->application_model->toggle_status($id, $new_status);

        if (!$success && !isset($application->active)) {
            // Active column doesn't exist
            echo json_encode([
                'success' => false,
                'message' => 'Database migration required. Please run the SQL script to add the active column to the applications table.'
            ]);
            die;
        }

        echo json_encode([
            'success' => $success,
            'message' => $success ? 'Application status updated successfully' : 'Failed to update application status'
        ]);
        die;
    }

    /* Force deactivate application (bypass confirmation) */
    public function force_deactivate($id)
    {
        if (!$id || !$this->input->is_ajax_request()) {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid request'
            ]);
            die;
        }

        $application = $this->application_model->get($id);
        if (!$application) {
            echo json_encode([
                'success' => false,
                'message' => 'Application not found'
            ]);
            die;
        }

        $active_linked_services = $this->application_model->count_active_linked_services($id);
        $success = $this->application_model->toggle_status($id, 0);

        if (!$success && !isset($application->active)) {
            echo json_encode([
                'success' => false,
                'message' => 'Database migration required. Please run the SQL script to add the active column to the applications table.'
            ]);
            die;
        }

        // If the application was already inactive, treat the operation as successful
        if (!$success && isset($application->active) && (int) $application->active === 0) {
            $success = true;
        }

        $services_deactivated = 0;
        if ($success && $active_linked_services > 0) {
            $services_deactivated = $this->application_model->deactivate_linked_services($id);
        }

        $message = $success ? 'Application deactivated successfully' : 'Failed to deactivate application';
        if ($success) {
            if ($services_deactivated > 0) {
                $message .= ' (' . $services_deactivated . ' linked service' . ($services_deactivated !== 1 ? 's were' : ' was') . ' set to inactive)';
            } elseif ($active_linked_services > 0) {
                $message .= ' (linked services were already inactive)';
            }
        }

        echo json_encode([
            'success' => $success,
            'message' => $message,
            'services_deactivated' => $services_deactivated,
            'previous_active_linked_services' => $active_linked_services,
        ]);
        die;
    }

}
