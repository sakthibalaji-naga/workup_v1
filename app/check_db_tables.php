<?php
$config = include 'application/config/database.php';
$dbhost = $config['default']['hostname'];
$dbuser = $config['default']['username'];
$dbpass = $config['default']['password'];
$dbname = $config['default']['database'];

$conn = new mysqli($dbhost, $dbuser, $dbpass, $dbname);
if ($conn->connect_error) die('Connection failed');

$tables = ['tbldivisions', 'tbldepartment_divisions'];
foreach ($tables as $table) {
    echo "--- $table ---\n";
    $result = $conn->query("DESCRIBE $table");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            if ($row['Key'] == 'PRI') {
                echo "PRIMARY KEY: " . $row['Field'] . "\n";
            }
            echo $row['Field'] . " - " . $row['Type'] . "\n";
        }
        $result->free();
        echo "\n";
    }
}
$conn->close();
?>
