<?php
// Update OTP SMS to DLT registered template
// Update the SMS trigger message to use {otp_code} for DLT compliance

// Database configuration
$hostname = 'localhost'; // Local database hostname
$username = 'appuser';
$password = '8wN4!zK9@pL2mR5';
$database = 'appdb';

try {
    $pdo = new PDO("mysql:host=$hostname;dbname=$database;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $option_name = 'sms_trigger_staff_password_reset';

    // Get current value
    $stmt = $pdo->prepare("SELECT value FROM tbloptions WHERE name = ?");
    $stmt->execute([$option_name]);
    $current = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($current) {
        echo "Current SMS template: " . $current['value'] . "\n";

        // DLT registered template
        $dlt_template = "Your OTP Verification Code is otp_code. Do not share it with anyone.\n\nRegards,\nNaga Limited Customer Support";

        if ($current['value'] !== $dlt_template) {
            // Update the option with DLT registered template
            $update_stmt = $pdo->prepare("UPDATE tbloptions SET value = ? WHERE name = ?");
            $update_stmt->execute([$dlt_template, $option_name]);

            echo "✅ Updated SMS template to DLT registered format:\n" . $dlt_template . "\n";
            echo "OTP will now use {otp_code} for proper DLT compliance.\n";
        } else {
            echo "SMS template is already using DLT registered format.\n";
        }
    } else {
        echo "❌ SMS trigger option not found in database.\n";
    }

} catch(PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "\nPlease check the database configuration.\n";
}
?>
