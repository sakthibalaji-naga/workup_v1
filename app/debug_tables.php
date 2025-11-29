<?php
// Debug script to check all the reference tables used in API formatting
$host = 'db';
$user = 'appuser';
$pass = 'secret';
$dbname = 'appdb';

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

$tables = [
    'tbltickets_status' => 'ticketstatusid',
    'tbltickets_priorities' => 'priorityid',
    'tbldepartments' => 'departmentid',
    'tblservices' => 'serviceid',
    'tbldivisions' => 'divisionid',
    'tbldepartment_divisions' => 'id'  // We'll check this
];

echo "--- API Reference Tables Check ---\n\n";

foreach ($tables as $table => $idColumn) {
    echo "--- $table (using $idColumn) ---\n";

    // Check if table exists
    $existsResult = $conn->query("SHOW TABLES LIKE '$table'");
    if ($existsResult->num_rows == 0) {
        echo "❌ TABLE DOES NOT EXIST\n";
        echo "💡 Suggestion: Check if table name is correct. Maybe it's '{$table}s' or 'tbl_{$table}'?\n\n";
        continue;
    }

    // Get table structure
    $descResult = $conn->query("DESCRIBE $table");
    $columns = [];
    $primaryKey = null;
    echo "📋 Columns:\n";
    while ($row = $descResult->fetch_assoc()) {
        echo "  - {$row['Field']} ({$row['Type']})";
        if ($row['Key'] == 'PRI') {
            echo " ← PRIMARY KEY";
            $primaryKey = $row['Field'];
        }
        echo "\n";
        $columns[] = $row['Field'];
    }

    // Check if the expected ID column exists
    if (!in_array($idColumn, $columns)) {
        echo "⚠️  WARNING: Expected column '$idColumn' not found!\n";
        echo "💡 Actual primary key: '$primaryKey'\n";
        echo "💡 Available columns: " . implode(', ', $columns) . "\n";
    }

    // Check if 'name' column exists
    if (!in_array('name', $columns)) {
        echo "⚠️  WARNING: 'name' column not found! Available: " . implode(', ', $columns) . "\n";
    }

    // Sample data
    $countResult = $conn->query("SELECT COUNT(*) as count FROM $table");
    $count = $countResult->fetch_assoc()['count'];
    echo "📊 Records: $count\n";

    if ($count > 0) {
        $sampleResult = $conn->query("SELECT * FROM $table LIMIT 3");
        echo "🔍 Sample data:\n";
        while ($row = $sampleResult->fetch_assoc()) {
            $idVal = isset($row[$idColumn]) ? $row[$idColumn] : 'NULL';
            $nameVal = isset($row['name']) ? $row['name'] : 'NULL';
            echo "  $idColumn: $idVal → name: $nameVal\n";
        }
    }

    echo "\n";
}

echo "--- Cross-reference check ---\n";
echo "Ticket response had: department='1', status='3', priority='2', etc.\n";
echo "Let's verify some of these exist:\n\n";

// Test specific lookups
$tests = [
    ["SELECT name FROM tbldepartments WHERE departmentid = 1", "Department ID 1"],
    ["SELECT name FROM tbltickets_status WHERE ticketstatusid = 3", "Status ID 3"],
    ["SELECT name FROM tbltickets_priorities WHERE priorityid = 2", "Priority ID 2"]
];

foreach ($tests as $test) {
    list($query, $description) = $test;
    $result = $conn->query($query);
    if ($result && $row = $result->fetch_assoc()) {
        echo "✅ $description → {$row['name']}\n";
    } else {
        echo "❌ $description → NOT FOUND or ERROR\n";
    }
}

$conn->close();
?>
