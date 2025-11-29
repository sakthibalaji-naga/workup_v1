<?php
require_once 'application/config/database.php';
require_once 'application/config/config.php';

$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($db->connect_error) {
    die('Connection failed: ' . $db->connect_error);
}

echo "Checking departments table...\n";
$result = $db->query('SELECT departmentid, name FROM ' . db_prefix() . 'departments LIMIT 10');
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo 'ID: ' . $row['departmentid'] . ', Name: ' . $row['name'] . "\n";
    }
} else {
    echo "No departments found or table does not exist\n";
}

echo "\nChecking staff_departments table...\n";
$result = $db->query('SELECT * FROM ' . db_prefix() . 'staff_departments LIMIT 5');
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo 'Staff ID: ' . $row['staffid'] . ', Department ID: ' . $row['departmentid'] . "\n";
    }
} else {
    echo "No staff-department relationships found\n";
}

$db->close();
?>
