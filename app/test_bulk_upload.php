<?php
// Test script for bulk upload functionality
require_once 'application/config/database.php';
require_once 'application/config/config.php';

// Simulate the bulk upload process
echo "Testing bulk upload functionality...\n";

// Load the Staff controller
require_once 'application/controllers/admin/Staff.php';

try {
    // Create a mock CI instance
    $CI = new Staff();
    $CI->load->database();

    // Test CSV data
    $csv_data = [
        ['firstname', 'lastname', 'email', 'password', 'role', 'department', 'division', 'phone', 'active'],
        ['Test', 'User', 'test.user@example.com', 'password123', 'Staff', 'IT', 'Development', '+1234567890', '1']
    ];

    // Test the process_confirmed_data method
    $confirmed_data = [
        'headers' => $csv_data[0],
        'data' => [$csv_data[1]]
    ];

    $json_data = json_encode($confirmed_data);

    echo "Testing process_confirmed_data with sample data...\n";

    // Call the method
    $result = $CI->process_confirmed_data($json_data);

    if ($result['success']) {
        echo "SUCCESS: " . $result['imported'] . " staff members imported\n";
    } else {
        echo "ERROR: " . $result['error'] . "\n";
    }

} catch (Exception $e) {
    echo "EXCEPTION: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}
?>
