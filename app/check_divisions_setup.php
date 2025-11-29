<?php
/**
 * Simple script to check divisions table and API setup
 */

defined('BASEPATH') or exit('No direct script access allowed');

// Include the necessary files
require_once 'index.php';

echo "=== DIVISIONS API SETUP CHECK ===\n\n";

try {
    $CI =& get_instance();
    $CI->load->database();

    // Test 1: Database Connection
    echo "1. Database Connection: ";
    if ($CI->db->conn_id) {
        echo "✓ CONNECTED\n";
    } else {
        echo "✗ FAILED\n";
        exit(1);
    }

    // Test 2: Check divisions table
    echo "2. Divisions Table (tbldivisions): ";
    if ($CI->db->table_exists('tbldivisions')) {
        echo "✓ EXISTS\n";

        $count = $CI->db->count_all('tbldivisions');
        echo "   - Records: $count\n";

        if ($count > 0) {
            $sample = $CI->db->select('divisionid, name')->limit(2)->get('tbldivisions')->result_array();
            echo "   - Sample data:\n";
            foreach ($sample as $row) {
                echo "     * ID: {$row['divisionid']}, Name: {$row['name']}\n";
            }
        }
    } else {
        echo "✗ NOT FOUND\n";
        echo "   This is likely causing the 500 error!\n";
    }

    // Test 3: Check API users table
    echo "3. API Users Table (tbl_api_users): ";
    if ($CI->db->table_exists('tbl_api_users')) {
        echo "✓ EXISTS\n";

        $count = $CI->db->count_all('tbl_api_users');
        echo "   - Records: $count\n";

        if ($count > 0) {
            $sample = $CI->db->select('username, api_key, perm_get')->limit(2)->get('tbl_api_users')->result_array();
            echo "   - Sample users:\n";
            foreach ($sample as $row) {
                echo "     * User: {$row['username']}, GET Permission: {$row['perm_get']}\n";
            }
        }
    } else {
        echo "✗ NOT FOUND\n";
    }

    // Test 4: Test API key validation
    echo "4. API Key Validation: ";
    $test_api_key = 'b9378ee5132537671c38e0eb8aceb1ca'; // The key that works for other endpoints

    // Note: Database password updated to: 8wN4!zK9@pL2mR5
    $CI->db->where('api_key', $test_api_key);
    $user = $CI->db->get('tbl_api_users')->row();

    if ($user) {
        echo "✓ VALID\n";
        echo "   - Username: {$user->username}\n";
        echo "   - GET Permission: " . ($user->perm_get ? 'YES' : 'NO') . "\n";
    } else {
        echo "✗ INVALID\n";
        echo "   The API key is not found in the database\n";
    }

    echo "\n=== SUMMARY ===\n";
    if ($CI->db->table_exists('tbldivisions') && $user && $user->perm_get) {
        echo "✓ All checks passed! The API should work.\n";
        echo "If you're still getting 500 errors, check:\n";
        echo "1. Database credentials in production\n";
        echo "2. PHP error logs for more details\n";
        echo "3. Web server configuration\n";
    } else {
        echo "✗ Issues found that need to be fixed:\n";
        if (!$CI->db->table_exists('tbldivisions')) {
            echo "- Create the tbldivisions table\n";
        }
        if (!$user) {
            echo "- Add the API key to tbl_api_users table\n";
        }
        if ($user && !$user->perm_get) {
            echo "- Grant GET permission to the API user\n";
        }
    }

} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "Check your database configuration and connection.\n";
}
?>
