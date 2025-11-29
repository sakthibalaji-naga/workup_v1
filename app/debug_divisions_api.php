<?php
/**
 * Debug script for divisions API 500 error
 * This script will help identify the root cause of the issue
 */

defined('BASEPATH') or exit('No direct script access allowed');

// Include the necessary files
require_once 'index.php';

// Test database connection
echo "=== DATABASE CONNECTION TEST ===\n";
try {
    $CI =& get_instance();
    $CI->load->database();

    if ($CI->db->conn_id) {
        echo "✓ Database connection successful\n";
    } else {
        echo "✗ Database connection failed\n";
        exit(1);
    }
} catch (Exception $e) {
    echo "✗ Database connection error: " . $e->getMessage() . "\n";
    exit(1);
}

// Test if required tables exist
echo "\n=== TABLE EXISTENCE TEST ===\n";
$required_tables = ['tbldivisions', 'tbl_api_users', 'tbl_api_logs'];

foreach ($required_tables as $table) {
    $exists = $CI->db->table_exists($table);
    echo ($exists ? "✓" : "✗") . " Table '$table' " . ($exists ? "exists" : "NOT FOUND") . "\n";

    if (!$exists && $table === 'tbldivisions') {
        echo "  This is likely the main cause of the 500 error!\n";
    }
}

// Test divisions table structure and data
echo "\n=== DIVISIONS TABLE TEST ===\n";
if ($CI->db->table_exists('tbldivisions')) {
    // Check table structure
    $fields = $CI->db->field_data('tbldivisions');
    echo "Table structure:\n";
    foreach ($fields as $field) {
        echo "  - {$field->name} ({$field->type}" . ($field->max_length ? "({$field->max_length})" : '') . ")\n";
    }

    // Check if table has data
    $count = $CI->db->count_all('tbldivisions');
    echo "\nTotal divisions in table: $count\n";

    if ($count > 0) {
        // Show sample data
        $sample = $CI->db->limit(3)->get('tbldivisions')->result_array();
        echo "Sample data:\n";
        foreach ($sample as $row) {
            echo "  - ID: {$row['divisionid']}, Name: {$row['name']}\n";
        }
    } else {
        echo "⚠ Warning: Table exists but is empty!\n";
    }
} else {
    echo "✗ Cannot test divisions table - it doesn't exist\n";
}

// Test API users table
echo "\n=== API USERS TABLE TEST ===\n";
if ($CI->db->table_exists('tbl_api_users')) {
    $count = $CI->db->count_all('tbl_api_users');
    echo "Total API users: $count\n";

    if ($count > 0) {
        $sample = $CI->db->select('username, api_key, perm_get')->limit(2)->get('tbl_api_users')->result_array();
        echo "Sample API users:\n";
        foreach ($sample as $row) {
            echo "  - Username: {$row['username']}, API Key: " . substr($row['api_key'], 0, 10) . "..., GET Permission: {$row['perm_get']}\n";
        }
    }
} else {
    echo "✗ API users table doesn't exist\n";
}

// Test API logs table
echo "\n=== API LOGS TABLE TEST ===\n";
if ($CI->db->table_exists('tbl_api_logs')) {
    $count = $CI->db->count_all('tbl_api_logs');
    echo "Total API log entries: $count\n";
} else {
    echo "API logs table doesn't exist (this is OK, it will be auto-created)\n";
}

// Test a simple API call simulation
echo "\n=== API CALL SIMULATION ===\n";
try {
    // Simulate the API key validation
    $test_api_key = 'test-key'; // Replace with actual API key if known
    $CI->db->where('api_key', $test_api_key);
    $user = $CI->db->get('tbl_api_users')->row();

    if ($user) {
        echo "✓ API key validation passed for user: {$user->username}\n";
        echo "  GET permission: " . ($user->perm_get ? 'YES' : 'NO') . "\n";
    } else {
        echo "✗ API key validation failed - user not found\n";
    }
} catch (Exception $e) {
    echo "✗ API key validation error: " . $e->getMessage() . "\n";
}

echo "\n=== DEBUGGING COMPLETE ===\n";
echo "If you see any ✗ marks above, those are likely causes of the 500 error.\n";
echo "Common solutions:\n";
echo "1. Create missing tables using SQL files in application/database/\n";
echo "2. Check database credentials in application/config/app-config.php\n";
echo "3. Verify API key exists and has proper permissions\n";
echo "4. Check if database server is running and accessible\n";
?>
