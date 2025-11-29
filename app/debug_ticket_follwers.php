<?php
// Debug script to check ticket followers
require_once 'index.php';

// Assuming we have a ticket ID from environment_details or test data
$ticket_id = 21; // From the URL in the task

$CI =& get_instance();
$CI->load->model('tickets_model');
$CI->load->model('tasks_model');
$CI->load->model('staff_model');

echo "=== TICKET FOLLOWERS DEBUG ===\n";
echo "Ticket ID: $ticket_id\n";

// Get ticket info
$ticket = $CI->tickets_model->get_ticket_by_id($ticket_id);
if ($ticket) {
    echo "\nTicket found:\n";
    echo "Assigned to: " . ($ticket->assigned ?? 'null') . "\n";
    echo "Admin (creator): " . ($ticket->admin ?? 'null') . "\n";
    echo "Created by: " . ($ticket->created_by ?? 'null') . "\n";

    // Get followers using the same method as the controller
    $followers = $CI->tickets_model->get_ticket_staff_followers($ticket_id, $ticket);
    echo "\nFollowers from get_ticket_staff_followers: " . json_encode($followers) . "\n";

    // Show names
    $staff_names = [];
    foreach ($followers as $staff_id) {
        $staff = $CI->staff_model->get($staff_id);
        $staff_names[] = $staff ? ($staff->firstname . ' ' . $staff->lastname) : "Staff #$staff_id";
    }
    echo "Staff names: " . implode(', ', $staff_names) . "\n";

    // Check if ticket handlers table exists
    $has_handlers_table = $CI->db->table_exists('ticket_handlers');
    echo "\nTicket handlers table exists: " . ($has_handlers_table ? 'YES' : 'NO') . "\n";

    if ($has_handlers_table) {
        // Get handlers for this ticket
        $handlers = $CI->tickets_model->get_ticket_handlers($ticket_id, false);
        echo "Handlers for ticket: " . json_encode($handlers) . "\n";

        $handler_names = [];
        foreach ($handlers as $handler_id) {
            $handler = $CI->staff_model->get($handler_id);
            $handler_names[] = $handler ? ($handler->firstname . ' ' . $handler->lastname) : "Handler #$handler_id";
        }
        echo "Handler names: " . implode(', ', $handler_names) . "\n";
    } else {
        echo "ERROR: ticket_handlers table does not exist!\n";
    }

} else {
    echo "\nTICKET NOT FOUND!\n";
}

// Test the build_ticket_conversion_prefill method (simplified test)
echo "\n=== TESTING build_ticket_conversion_prefill SIMULATION ===\n";

// Check if GET parameters are set
$ticket_to_task = isset($_GET['ticket_to_task']) && $_GET['ticket_to_task'] === '21';
$rel_type = isset($_GET['rel_type']) && $_GET['rel_type'] === 'ticket';
$rel_id = isset($_GET['rel_id']) && $_GET['rel_id'] === '21';

echo "URL params: ticket_to_task=$ticket_to_task, rel_type=$rel_type, rel_id=$rel_id\n";

// Simulate the prefilling
if ($ticket_to_task && $rel_type === 'ticket' && is_numeric($rel_id)) {
    $shouldPrefillFromTicket = true;
    echo "Should prefill from ticket: YES\n";

    $prefillId = (int) $_GET['rel_id'];
    $prefillName = trim((string) ($ticket->subject ?? ''));
    $prefillDescription = (string) ($ticket->message ?? '');

    $replyId = (int) ($_GET['ticket_reply_id'] ?? 0);
    if ($replyId > 0) {
        echo "Reply ID specified: $replyId - checking for reply content...\n";
        $CI->db->select('message');
        $CI->db->from(db_prefix() . 'ticket_replies');
        $CI->db->where('ticketid', $prefillId);
        $CI->db->where('id', $replyId);
        $reply = $CI->db->get()->row();
        if ($reply) {
            $prefillDescription = (string) $reply->message;
            echo "Using reply content for description\n";
        }
    }

    $prefillFollowers = $CI->tickets_model->get_ticket_staff_followers($prefillId, $ticket);

    $prefill = [
        'name' => $prefillName,
        'description' => $prefillDescription,
        'followers' => $prefillFollowers,
    ];

    echo "Prefill data: " . json_encode($prefill) . "\n";

} else {
    echo "Should prefill from ticket: NO\n";
}

echo "\n=== END DEBUG ===\n";
?>
