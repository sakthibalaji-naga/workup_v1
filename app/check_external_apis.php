<?php
// Simple script to check external APIs configuration
$config_file = 'application/config/database.php';
if (file_exists($config_file)) {
    include $config_file;

    try {
        $mysqli = new mysqli($db['default']['hostname'], $db['default']['username'], $db['default']['password'], $db['default']['database']);

        if ($mysqli->connect_error) {
            echo 'Database connection failed: ' . $mysqli->connect_error;
        } else {
            echo 'Database connected successfully' . PHP_EOL;

            // Check if external APIs table exists
            $result = $mysqli->query('SHOW TABLES LIKE "tbl_external_apis"');
            if ($result->num_rows > 0) {
                echo 'External APIs table exists' . PHP_EOL;

                // Get external APIs
                $apis = $mysqli->query('SELECT id, name, api_url, request_method, is_active FROM tbl_external_apis WHERE is_active = 1');
                if ($apis->num_rows > 0) {
                    echo 'Active External APIs:' . PHP_EOL;
                    while ($api = $apis->fetch_assoc()) {
                        echo 'ID: ' . $api['id'] . ', Name: ' . $api['name'] . ', URL: ' . $api['api_url'] . ', Method: ' . $api['request_method'] . PHP_EOL;
                    }
                } else {
                    echo 'No active external APIs found' . PHP_EOL;
                }
            } else {
                echo 'External APIs table does not exist' . PHP_EOL;
            }
        }

        $mysqli->close();
    } catch (Exception $e) {
        echo 'Error: ' . $e->getMessage();
    }
} else {
    echo 'Database config file not found';
}
?>
