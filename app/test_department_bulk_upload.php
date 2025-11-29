<?php
// Test script for department handling in bulk upload
require_once 'application/config/database.php';
require_once 'application/config/config.php';

echo "Testing department handling in bulk upload...\n";

// Load the Staff controller
require_once 'application/controllers/admin/Staff.php';

try {
    // Create a mock CI instance
    $CI = new Staff();
    $CI->load->database();

    // Test CSV data with departments
    $csv_data = [
        ['firstname', 'lastname', 'email', 'emp_code', 'password', 'role', 'department', 'division', 'phone', 'active', 'reporting_manager', 'sub_department', 'send_welcome_email'],
        ['Test', 'User', 'test.user@example.com', 'EMP001', 'password123', 'Staff', 'IT', 'Development', '+1234567890', '1', '', 'HR', '1']
    ];

    // Test the map_csv_row_to_staff_data method
    $staff_data = $CI->map_csv_row_to_staff_data($csv_data[1], $csv_data[0]);

    echo "Mapped staff data:\n";
    print_r($staff_data);

    // Check if departments are included
    if (isset($staff_data['departments'])) {
        echo "\nDepartments found: " . implode(', ', $staff_data['departments']) . "\n";

        // Check department IDs
        foreach ($staff_data['departments'] as $dept_id) {
            $CI->db->select('name');
            $CI->db->from(db_prefix() . 'departments');
            $CI->db->where('departmentid', $dept_id);
            $dept = $CI->db->get()->row();
            if ($dept) {
                echo "Department ID $dept_id = {$dept->name}\n";
            } else {
                echo "Department ID $dept_id not found\n";
            }
        }
    } else {
        echo "\nNo departments found in staff data\n";
    }

} catch (Exception $e) {
    echo "EXCEPTION: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}
?>
