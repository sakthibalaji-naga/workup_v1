<?php
// Script to manually set ticket_number for ticket ID 5
require_once 'index.php';
$CI =& get_instance();

$query = $CI->db->query("SELECT ticketid, ticket_number FROM " . db_prefix() . "tickets WHERE ticketid = 5");
$row = $query->row();
print_r($row);

if ($row) {
    if (empty($row->ticket_number)) {
        $CI->db->where('ticketid', 5)->update(db_prefix() . 'tickets', ['ticket_number' => '2500005']);
        echo "\nUpdated ticket 5 to have ticket_number 2500005\n";
    } elseif ($row->ticket_number === '2500005') {
        echo "\nTicket 5 already has correct ticket_number\n";
    } else {
        echo "\nTicket 5 has ticket_number: " . $row->ticket_number . "\n";
    }
} else {
    echo "\nTicket 5 not found\n";
}
