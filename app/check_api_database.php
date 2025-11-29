<?php
// Direct MySQL check script - bypass CodeIgniter security
$host = 'db';
$user = 'appuser';
$pass = 'secret';
$dbname = 'appdb';

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

echo "--- Checking tbl_api_logs table ---\n";

// Check if table exists
$result = $conn->query("SHOW TABLES LIKE 'tbl_api_logs'");
if ($result->num_rows == 0) {
    echo "❌ Table tbl_api_logs does NOT exist!\n";
    echo "\n📋 SQL to create the table:\n";
    echo "USE appdb;\n";
    echo "CREATE TABLE IF NOT EXISTS tbl_api_logs (\n";
    echo "  id int(11) AUTO_INCREMENT PRIMARY KEY,\n";
    echo "  endpoint varchar(255),\n";
    echo "  method varchar(10),\n";
    echo "  api_key varchar(255),\n";
    echo "  request_body text,\n";
    echo "  response_body text,\n";
    echo "  status_code int(11),\n";
    echo "  created_at timestamp DEFAULT CURRENT_TIMESTAMP\n";
    echo ") ENGINE=InnoDB DEFAULT CHARSET=utf8;\n";
    echo "\n-- Add indexes\n";
    echo "ALTER TABLE tbl_api_logs ADD INDEX idx_api_key (api_key);\n";
    echo "ALTER TABLE tbl_api_logs ADD INDEX idx_created_at (created_at);\n";
} else {
    echo "✅ Table tbl_api_logs exists!\n";

    // Check column structure
    echo "\n--- Table Structure ---\n";
    $desc = $conn->query("DESCRIBE tbl_api_logs");
    while ($row = $desc->fetch_assoc()) {
        echo $row['Field'] . " - " . $row['Type'] . " - " . ($row['Key'] == 'PRI' ? 'PRIMARY KEY' : '') . "\n";
    }

    // Check if it has data
    echo "\n--- Data Check ---\n";
    $countResult = $conn->query("SELECT COUNT(*) as count FROM tbl_api_logs");
    $count = $countResult->fetch_assoc()['count'];
    echo "📊 Total records: $count\n";

    if ($count > 0) {
        // Show sample data
        echo "\n--- Recent records ---\n";
        $sample = $conn->query("SELECT id, endpoint, method, LEFT(response_body, 50) as response_preview, created_at FROM tbl_api_logs ORDER BY created_at DESC LIMIT 3");
        while ($row = $sample->fetch_assoc()) {
            echo "ID: {$row['id']} | Method: {$row['method']} | Endpoint: {$row['endpoint']}\n";
            echo "Response: {$row['response_preview']}...\n";
            echo "Created: {$row['created_at']}\n\n";
        }
    } else {
        echo "📝 Table is empty - no API logs yet.\n";
        echo "Make some API calls through the endpoints to populate logs.\n";
    }
}

$conn->close();
?>
