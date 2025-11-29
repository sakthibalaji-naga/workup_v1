<?php
/**
 * Production Debug Script for Divisions API
 * Run this on your production server to check logs and identify issues
 */

echo "=== PRODUCTION DIVISIONS API DEBUG ===\n";
echo "Run this script on your production server to diagnose issues.\n\n";

// Check if we're in the right directory
if (!file_exists('index.php')) {
    echo "ERROR: Please run this script from your application root directory.\n";
    exit(1);
}

// Check PHP version
echo "1. PHP Version: " . PHP_VERSION . "\n";

// Check if CodeIgniter can be loaded
echo "2. CodeIgniter Loading: ";
try {
    require_once 'index.php';
    echo "✓ SUCCESS\n";
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    exit(1);
}

// Check database connection
echo "3. Database Connection: ";
try {
    $CI =& get_instance();
    $CI->load->database();

    if ($CI->db->conn_id) {
        echo "✓ CONNECTED\n";
        echo "   - Host: " . $CI->db->hostname . "\n";
        echo "   - Database: " . $CI->db->database . "\n";
        echo "   - User: " . $CI->db->username . "\n";
    } else {
        echo "✗ FAILED\n";
    }
} catch (Exception $e) {
    echo "✗ ERROR: " . $e->getMessage() . "\n";
}

// Check required tables
echo "4. Required Tables:\n";
$tables = ['tbldivisions', 'tbl_api_users'];

foreach ($tables as $table) {
    echo "   - $table: ";
    try {
        if ($CI->db->table_exists($table)) {
            $count = $CI->db->count_all($table);
            echo "✓ EXISTS ($count records)\n";
        } else {
            echo "✗ MISSING\n";
        }
    } catch (Exception $e) {
        echo "✗ ERROR: " . $e->getMessage() . "\n";
    }
}

// Check API logs table
echo "5. API Logs Table: ";
try {
    if ($CI->db->table_exists('tbl_api_logs')) {
        $count = $CI->db->count_all('tbl_api_logs');
        echo "✓ EXISTS ($count records)\n";

        // Show recent divisions API calls
        echo "   Recent divisions API calls:\n";
        $recent = $CI->db->where('endpoint', '/api/v1/get_divisions')
                        ->order_by('created_at', 'DESC')
                        ->limit(5)
                        ->get('tbl_api_logs')
                        ->result_array();

        if (count($recent) > 0) {
            foreach ($recent as $log) {
                echo "   - " . $log['created_at'] . " | Status: " . $log['status_code'];
                if ($log['username']) {
                    echo " | User: " . $log['username'];
                }
                echo "\n";
            }
        } else {
            echo "   - No recent calls found\n";
        }
    } else {
        echo "✗ MISSING (will be auto-created)\n";
    }
} catch (Exception $e) {
    echo "✗ ERROR: " . $e->getMessage() . "\n";
}

// Check application/logs directory
echo "6. Log Files:\n";
$log_dir = 'application/logs/';
if (is_dir($log_dir)) {
    echo "   - Log directory: ✓ EXISTS\n";

    $log_files = glob($log_dir . 'log-*.php');
    if (count($log_files) > 0) {
        $latest_log = $log_files[count($log_files) - 1];
        echo "   - Latest log: " . basename($latest_log) . "\n";

        // Show recent divisions API errors
        echo "   - Recent divisions API errors:\n";
        $log_content = file_get_contents($latest_log);
        $divisions_errors = [];

        $lines = explode("\n", $log_content);
        foreach ($lines as $line) {
            if (strpos($line, 'div_api_') !== false && (strpos($line, 'ERROR') !== false || strpos($line, 'CRITICAL') !== false)) {
                $divisions_errors[] = trim($line);
            }
        }

        if (count($divisions_errors) > 0) {
            foreach (array_slice($divisions_errors, -5) as $error) {
                echo "     * " . str_replace('ERROR - ', '', $error) . "\n";
            }
        } else {
            echo "     * No recent divisions API errors found\n";
        }
    } else {
        echo "   - No log files found\n";
    }
} else {
    echo "   - Log directory: ✗ NOT FOUND\n";
}

// Test API endpoint directly
echo "7. Direct API Test:\n";
echo "   To test the API endpoint, run:\n";
echo "   curl -H 'X-API-Key: YOUR_API_KEY' https://yourdomain.com/api/v1/get_divisions\n";
echo "   \n";
echo "   Or use this PHP test:\n";
echo "   <?php\n";
echo "   \$ch = curl_init();\n";
echo "   curl_setopt(\$ch, CURLOPT_URL, 'https://yourdomain.com/api/v1/get_divisions');\n";
echo "   curl_setopt(\$ch, CURLOPT_HTTPHEADER, ['X-API-Key: YOUR_API_KEY']);\n";
echo "   curl_setopt(\$ch, CURLOPT_RETURNTRANSFER, true);\n";
echo "   \$response = curl_exec(\$ch);\n";
echo "   \$http_code = curl_getinfo(\$ch, CURLINFO_HTTP_CODE);\n";
echo "   curl_close(\$ch);\n";
echo "   echo \"HTTP Code: \$http_code\\n\";\n";
echo "   echo \"Response: \$response\\n\";\n";
echo "   ?>\n";

echo "\n=== DEBUG COMPLETE ===\n";
echo "Check the output above for any ✗ marks - these indicate potential issues.\n";
echo "Also check your web server error logs for PHP fatal errors.\n";
?>
