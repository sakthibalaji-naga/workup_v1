<?php
/**
 * Check recent divisions API logs and errors
 */

echo "=== DIVISIONS API LOG ANALYSIS ===\n\n";

// Check if we're in the right directory
if (!file_exists('application/logs/')) {
    echo "ERROR: application/logs/ directory not found.\n";
    exit(1);
}

// Find the latest log file
$log_files = glob('application/logs/log-*.php');
if (empty($log_files)) {
    echo "No log files found in application/logs/\n";
    exit(1);
}

$latest_log = $log_files[count($log_files) - 1];
echo "Analyzing latest log file: " . basename($latest_log) . "\n\n";

// Read the log file
$log_content = file_get_contents($latest_log);

// Look for divisions API related entries
echo "=== DIVISIONS API REQUESTS (Last 20) ===\n";
$divisions_requests = [];
$lines = explode("\n", $log_content);

foreach ($lines as $line) {
    if (strpos($line, 'div_api_') !== false) {
        $divisions_requests[] = trim($line);
    }
}

if (count($divisions_requests) > 0) {
    foreach (array_slice($divisions_requests, -20) as $request) {
        echo str_replace('INFO - ', '', $request) . "\n";
    }
} else {
    echo "No divisions API requests found in logs.\n";
}

echo "\n=== DIVISIONS API ERRORS (Last 10) ===\n";
$divisions_errors = [];

foreach ($lines as $line) {
    if (strpos($line, 'div_api_') !== false && (strpos($line, 'ERROR') !== false || strpos($line, 'CRITICAL') !== false)) {
        $divisions_errors[] = trim($line);
    }
}

if (count($divisions_errors) > 0) {
    foreach (array_slice($divisions_errors, -10) as $error) {
        echo str_replace(['ERROR - ', 'CRITICAL - '], '', $error) . "\n";
    }
} else {
    echo "No divisions API errors found in logs.\n";
}

echo "\n=== SUMMARY ===\n";
echo "Total divisions API requests found: " . count($divisions_requests) . "\n";
echo "Total divisions API errors found: " . count($divisions_errors) . "\n";

if (count($divisions_errors) > 0) {
    echo "\n⚠️  ERRORS DETECTED! Check the error messages above.\n";
    echo "Common solutions:\n";
    echo "- Check if tbldivisions table exists\n";
    echo "- Verify API key has proper permissions\n";
    echo "- Check database connection\n";
    echo "- Review PHP error logs for fatal errors\n";
} else {
    echo "\n✅ No errors found in recent logs.\n";
    echo "If you're still getting 500 errors, check:\n";
    echo "- Web server error logs\n";
    echo "- PHP fatal errors (may not appear in CodeIgniter logs)\n";
    echo "- Database connection issues\n";
}

echo "\n=== NEXT STEPS ===\n";
echo "1. Run 'php production_debug.php' for comprehensive diagnostics\n";
echo "2. Check web server error logs (Apache/Nginx)\n";
echo "3. Verify database credentials and connectivity\n";
echo "4. Test API endpoint with curl or Postman\n";
?>
