<?php
// Simple check for table existence and data structure
$db_config = include('application/config/database.php');

$host = $db_config['default']['hostname'];
$username = $db_config['default']['username'];
$password = $db_config['default']['password'];
$database = $db_config['default']['database'];

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h2>Ticket Reassignment System Check</h2>";

// Check if ticket_reassignments table exists
$result = $conn->query("SHOW TABLES LIKE 'tblticket_reassignments'");
if ($result->num_rows > 0) {
    echo "✓ ticket_reassignments table exists (prefixed)<br>";
} else {
    echo "✗ ticket_reassignments table (prefixed) missing<br>";
}

// Check without prefix
$result = $conn->query("SHOW TABLES LIKE 'ticket_reassignments'");
if ($result->num_rows > 0) {
    echo "✓ ticket_reassignments table exists<br>";

    // Check table structure
    $result = $conn->query("DESCRIBE ticket_reassignments");
    echo "<h3>Table Structure:</h3><pre>";
    while ($row = $result->fetch_assoc()) {
        print_r($row);
    }
    echo "</pre>";
} else {
    echo "✗ ticket_reassignments table missing<br>";
}

// Check tickets table
$result = $conn->query("SHOW TABLES LIKE 'tbltickets'");
if ($result->num_rows > 0) {
    echo "✓ tickets table exists<br>";

    // Count tickets
    $result = $conn->query("SELECT COUNT(*) as total FROM tbltickets");
    $count = $result->fetch_assoc();
    echo "✓ Total tickets: " . $count['total'] . "<br>";
} else {
    echo "✗ tickets table missing<br>";
}

// Check staff table
$result = $conn->query("SHOW TABLES LIKE 'tblstaff'");
if ($result->num_rows > 0) {
    echo "✓ staff table exists<br>";

    // Count active staff
    $result = $conn->query("SELECT COUNT(*) as total FROM tblstaff WHERE active = 1");
    $count = $result->fetch_assoc();
    echo "✓ Active staff: " . $count['total'] . "<br>";
} else {
    echo "✗ staff table missing<br>";
}

$conn->close();
?>
