<?php
// Add these methods to your Tickets controller

public function get_service_options($type)
{
    if (!has_permission('tickets', '', 'view')) {
        ajax_access_denied();
    }

    $this->load->model('ticket_service_options_model');
    $options = $this->ticket_service_options_model->get_options($type);
    
    echo json_encode($options);
}

public function add_service_option()
{
    if (!has_permission('tickets', '', 'create')) {
        ajax_access_denied();
    }

    $type = $this->input->post('type');
    $name = $this->input->post('name');

    if (empty($type) || empty($name)) {
        echo json_encode(['success' => false]);
        return;
    }

    $this->load->model('ticket_service_options_model');
    $id = $this->ticket_service_options_model->add_option([
        'type' => $type,
        'name' => $name
    ]);

    echo json_encode(['success' => true, 'id' => $id]);
}

public function update_service_option()
{
    if (!has_permission('tickets', '', 'edit')) {
        ajax_access_denied();
    }

    $id = $this->input->post('id');
    $type = $this->input->post('type');
    $name = $this->input->post('name');

    if (empty($id) || empty($type) || empty($name)) {
        echo json_encode(['success' => false]);
        return;
    }

    $this->load->model('ticket_service_options_model');
    $success = $this->ticket_service_options_model->update_option($id, [
        'type' => $type,
        'name' => $name
    ]);

    echo json_encode(['success' => $success]);
}

public function delete_service_option()
{
    if (!has_permission('tickets', '', 'delete')) {
        ajax_access_denied();
    }

    $id = $this->input->post('id');
    if (empty($id)) {
        echo json_encode(['success' => false]);
        return;
    }

    $this->load->model('ticket_service_options_model');
    $success = $this->ticket_service_options_model->delete_option($id);

    echo json_encode(['success' => $success]);
}
