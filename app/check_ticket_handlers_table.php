<?php
// Simple script to check ticket_handlers table
include 'src/index.php';

$CI =& get_instance();
$CI->load->database();

$table_name = 'tblticket_handlers';
$table_exists = $CI->db->table_exists($table_name);

echo "Table '$table_name' exists: " . ($table_exists ? 'YES' : 'NO') . "\n";
echo "Database error: " . json_encode($CI->db->error()) . "\n";

// Also check if we can create the table
if (!$table_exists) {
    echo "Attempting to create table...\n";
    $sql = "CREATE TABLE IF NOT EXISTS `$table_name` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `ticketid` INT(11) NOT NULL,
        `staffid` INT(11) NOT NULL,
        `created_at` DATETIME NULL,
        PRIMARY KEY (`id`),
        KEY `ticketid` (`ticketid`),
        KEY `staffid` (`staffid`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

    $result = $CI->db->query($sql);
    $error = $CI->db->error();

    if ($error['code'] == 0) {
        echo "Table created successfully!\n";
    } else {
        echo "Failed to create table. Error: " . json_encode($error) . "\n";
    }
}

// Check if table exists now
$table_exists_after = $CI->db->table_exists($table_name);
echo "Table '$table_name' exists after attempt: " . ($table_exists_after ? 'YES' : 'NO') . "\n";
