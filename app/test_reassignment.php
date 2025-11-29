<?php
require_once 'index.php';

echo "<h2>Testing Ticket Reassignment System</h2>";

// Initialize system (similar to CI bootstrap)
define('ENVIRONMENT', 'development');
define('BASEPATH', dirname(__FILE__) . '/system/');
define('APPPATH', dirname(__FILE__) . '/application/');
define('FCPATH', dirname(__FILE__) . '/');

require_once BASEPATH . 'core/Common.php';
$CFG =& load_class('Config', 'core');
$UNI =& load_class('UTF8', 'core');
$SEC =& load_class('Security', 'core');
$IN =& load_class('Input', 'core');
$db =& load_class('DB', null, $CFG);
$session =& load_class('Session', 'libraries');
$hooks =& load_class('Hooks', 'core');

// Load the tickets model
$CI =& get_instance();
$CI->load->model('tickets_model');

echo "<h3>Testing Database Connection</h3>";
if ($CI->db->table_exists(db_prefix() . 'tickets')) {
    echo "✓ Tickets table exists<br>";
} else {
    echo "✗ Tickets table missing<br>";
}

if ($CI->db->table_exists(db_prefix() . 'ticket_reassignments')) {
    echo "✓ ticket_reassignments table exists<br>";

    // Show table structure
    echo "<h4>Table Structure:</h4>";
    $fields = $CI->db->list_fields(db_prefix() . 'ticket_reassignments');
    echo "<pre>";
    print_r($fields);
    echo "</pre>";
} else {
    echo "✗ ticket_reassignments table missing - creating...<br>";

    // Try to create the table manually
    $create_sql = "CREATE TABLE IF NOT EXISTS `" . db_prefix() . "ticket_reassignments` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `ticketid` INT(11) NOT NULL,
        `divisionid` INT(11) NULL,
        `department` INT(11) NULL,
        `sub_department` INT(11) NULL,
        `service` INT(11) NULL,
        `from_assigned` INT(11) NULL,
        `to_assigned` INT(11) NOT NULL,
        `status` VARCHAR(20) NOT NULL DEFAULT 'pending',
        `created_by` INT(11) NOT NULL,
        `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `expires_at` DATETIME NULL,
        `decision_by` INT(11) NULL,
        `decision_at` DATETIME NULL,
        `decision_remarks` TEXT NULL,
        PRIMARY KEY (`id`),
        KEY `ticketid` (`ticketid`),
        KEY `to_assigned` (`to_assigned`),
        KEY `status` (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

    if ($CI->db->query($create_sql)) {
        echo "✓ Table created successfully<br>";
    } else {
        echo "✗ Failed to create table: " . $CI->db->error()['message'] . "<br>";
    }
}

// Test with a sample ticket (if exists)
echo "<h3>Testing Reassignment Creation</h3>";
$sample_ticket = $CI->db->limit(1)->get(db_prefix() . 'tickets')->row();

if ($sample_ticket) {
    echo "Found ticket ID: {$sample_ticket->ticketid}<br>";

    // Get another staff member as target
    $other_staff = $CI->db->where('active', 1)->limit(1)->get(db_prefix() . 'staff')->row();
    if ($other_staff && $other_staff->staffid != 1) { // Avoid admin if possible
        echo "Using staff ID {$other_staff->staffid} as reassignment target<br>";

        $result = $CI->tickets_model->create_reassign_request([
            'ticketid' => $sample_ticket->ticketid,
            'from_assigned' => $sample_ticket->assigned ?? 0,
            'to_assigned' => $other_staff->staffid,
            'divisionid' => $sample_ticket->divisionid ?? null,
            'department' => $sample_ticket->department ?? null,
            'sub_department' => $sample_ticket->sub_department ?? null,
            'application_id' => null,
            'service' => $sample_ticket->service ?? null,
        ]);

        if ($result === true) {
            echo "✓ Reassignment request created successfully<br>";
        } elseif (is_string($result)) {
            echo "✗ Error: {$result}<br>";
        } else {
            echo "✗ Failed for unknown reason<br>";
        }
    } else {
        echo "No suitable target staff found<br>";
    }
} else {
    echo "No tickets found in database<br>";
}
?>
