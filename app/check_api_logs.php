<?php
// Simple script to check if tbl_api_logs table exists and has data
// Use direct database credentials instead of CodeIgniter config
$dbhost = 'localhost';  // Use localhost since MySQL port is exposed to host
$dbuser = 'appuser';  // From app-config.php
$dbpass = 'secret';  // From app-config.php
$dbname = 'appdb';  // From app-config.php

$conn = new mysqli($dbhost, $dbuser, $dbpass, $dbname);
if ($conn->connect_error) die('Connection failed: ' . $conn->connect_error);

echo "--- Checking tbl_api_logs table ---\n";

// Check if table exists
$result = $conn->query("SHOW TABLES LIKE 'tbl_api_logs'");
if ($result->num_rows == 0) {
    echo "❌ Table tbl_api_logs does NOT exist!\n";
    echo "Please run this SQL to create it:\n";
    echo "CREATE TABLE tbl_api_logs (\n";
    echo "  id int(11) AUTO_INCREMENT PRIMARY KEY,\n";
    echo "  endpoint varchar(255),\n";
    echo "  method varchar(10),\n";
    echo "  api_key varchar(255),\n";
    echo "  request_body text,\n";
    echo "  response_body text,\n";
    echo "  status_code int(11),\n";
    echo "  created_at timestamp DEFAULT CURRENT_TIMESTAMP\n";
    echo ") ENGINE=InnoDB DEFAULT CHARSET=utf8;\n";
} else {
    echo "✅ Table tbl_api_logs exists!\n";

    // Check if it has data
    $countResult = $conn->query("SELECT COUNT(*) as count FROM tbl_api_logs");
    $count = $countResult->fetch_assoc()['count'];
    echo "📊 Records in table: $count\n";

    if ($count > 0) {
        // Show sample data
        echo "\n--- Sample records ---\n";
        $sample = $conn->query("SELECT * FROM tbl_api_logs ORDER BY created_at DESC LIMIT 3");
        while ($row = $sample->fetch_assoc()) {
            echo "ID: {$row['id']}, Endpoint: {$row['endpoint']}, Method: {$row['method']}, Status: {$row['status_code']}, Created: {$row['created_at']}\n";
        }

        // Show table structure
        echo "\n--- Table structure ---\n";
        $structure = $conn->query("DESCRIBE tbl_api_logs");
        while ($field = $structure->fetch_assoc()) {
            echo "{$field['Field']} ({$field['Type']}) - {$field['Null']} - {$field['Key']}\n";
        }

        // Check for missing request_body field that API controller is trying to insert
        $checkField = $conn->query("SHOW COLUMNS FROM tbl_api_logs LIKE 'request_body'");
        if ($checkField->num_rows == 0) {
            echo "\n❌ ISSUE FOUND: 'request_body' field is missing from table!\n";
            echo "The API controller is trying to insert this field but it doesn't exist.\n";
            echo "This would cause API log insertions to fail.\n";

            // Offer to fix the issue
            echo "\n🔧 FIXING: Adding missing 'request_body' field to table...\n";
            $addField = $conn->query("ALTER TABLE tbl_api_logs ADD COLUMN request_body TEXT AFTER api_key");
            if ($addField) {
                echo "✅ Successfully added 'request_body' field to tbl_api_logs table!\n";
            } else {
                echo "❌ Failed to add field: " . $conn->error . "\n";
            }
        } else {
            echo "\n✅ All required fields are present in the table.\n";

            // Show updated table structure to confirm fix
            echo "\n--- Updated table structure ---\n";
            $updatedStructure = $conn->query("DESCRIBE tbl_api_logs");
            while ($field = $updatedStructure->fetch_assoc()) {
                echo "{$field['Field']} ({$field['Type']}) - {$field['Null']} - {$field['Key']}\n";
            }
        }
    }
}

$conn->close();
?>
