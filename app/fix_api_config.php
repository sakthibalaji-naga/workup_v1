<?php
// Fix API Configuration Script
// Run this script to fix your ZingHR API configuration

// Database configuration - update these with your actual database details
$hostname = 'localhost';
$username = 'your_db_username';
$password = 'your_db_password';
$database = 'your_db_name';

try {
    $pdo = new PDO("mysql:host=$hostname;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if the API already exists
    $check_stmt = $pdo->prepare("SELECT id FROM tbl_external_apis WHERE api_url = ?");
    $check_stmt->execute(['https://portal.zinghr.com/2015/route/EmployeeDetails/GetEmployeeMasterDetails']);
    $existing_api = $check_stmt->fetch(PDO::FETCH_ASSOC);

    $headers = json_encode([
        'Content-Type' => 'application/json',
        'Cookie' => 'ASP.NET_SessionId=l4nh4upkfp2wjafioweo5w1k; BNI_persistence=t3S_ZqsyV_T2UPQov9UE8BF89d6cr8QkMw-VGf0HFRA4cFp6iTGagLYiGFlpKADa6iZobSzjFeqCCaAorDNeWA=='
    ]);

    $request_body = json_encode([
        'SubcriptionName' => 'NAGA',
        'Token' => 'a2a92b65595b431b9a55993f239e3046',
        'EmployeeCode' => '10379'
    ]);

    if ($existing_api) {
        // Update existing API
        $update_stmt = $pdo->prepare("
            UPDATE tbl_external_apis SET
            name = ?,
            request_method = ?,
            request_body = ?,
            headers = ?,
            cron_schedule = ?,
            is_active = ?,
            next_run = ?
            WHERE id = ?
        ");

        $update_stmt->execute([
            'ZingHR Employee API',
            'POST',
            $request_body,
            $headers,
            '0 */6 * * *', // Every 6 hours
            1, // Active
            date('Y-m-d H:i:s', strtotime('+6 hours')),
            $existing_api['id']
        ]);

        echo "✅ Updated existing ZingHR API configuration (ID: " . $existing_api['id'] . ")\n";
    } else {
        // Insert new API
        $insert_stmt = $pdo->prepare("
            INSERT INTO tbl_external_apis (
                name, api_url, request_method, request_body, headers,
                cron_schedule, is_active, created_at, next_run
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $insert_stmt->execute([
            'ZingHR Employee API',
            'https://portal.zinghr.com/2015/route/EmployeeDetails/GetEmployeeMasterDetails',
            'POST',
            $request_body,
            $headers,
            '0 */6 * * *', // Every 6 hours
            1, // Active
            date('Y-m-d H:i:s'),
            date('Y-m-d H:i:s', strtotime('+6 hours'))
        ]);

        $new_id = $pdo->lastInsertId();
        echo "✅ Created new ZingHR API configuration (ID: $new_id)\n";
    }

    echo "\n🎉 API Configuration Fixed!\n";
    echo "You can now use this API in your Flow Builder.\n";
    echo "The API will work exactly like your curl command.\n";

} catch(PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "\nPlease update the database configuration at the top of this file.\n";
}
?>
