<?php
// Check and create approval flow tables if they don't exist
echo "<h1>Approval Flow Table Setup</h1>";

// Include the database configuration
require_once 'src/application/config/database.php';
require_once 'src/application/config/app-config.php';

// Define constants
define('BASEPATH', dirname(__FILE__) . '/src/system/');
define('APPPATH', dirname(__FILE__) . '/src/application/');
define('ENVIRONMENT', 'development');

// Function to check if table exists
function table_exists($table_name) {
    global $conn;
    $result = $conn->query("SHOW TABLES LIKE '$table_name'");
    return $result->num_rows > 0;
}

// Function to execute SQL
function execute_sql($sql) {
    global $conn;
    if ($conn->query($sql) === TRUE) {
        echo "<p style='color: green;'>✓ Success: " . substr($sql, 0, 50) . "...</p>";
        return true;
    } else {
        echo "<p style='color: red;'>✗ Error: " . $conn->error . "</p>";
        return false;
    }
}

// Create database connection
$host = APP_DB_HOSTNAME;
$user = APP_DB_USERNAME;
$pass = APP_DB_PASSWORD;
$dbname = APP_DB_NAME;

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("<p style='color: red;'>Connection failed: " . $conn->connect_error . "</p>");
}

echo "<p style='color: green;'>Connected to database successfully</p>";

// Check if tables exist
$flows_table = 'tblapproval_flows';
$steps_table = 'tblapproval_flow_steps';
$approvals_table = 'tbltask_approvals';
$history_table = 'tbltask_approval_remark_history';

$flows_exists = table_exists($flows_table);
$steps_exists = table_exists($steps_table);
$approvals_exists = table_exists($approvals_table);
$history_exists = table_exists($history_table);

// Check if status column exists in flows table
$flows_has_status = false;
if ($flows_exists) {
    $result = $conn->query("SHOW COLUMNS FROM `$flows_table` LIKE 'status'");
    $flows_has_status = $result->num_rows > 0;
}

echo "<h2>Table Status:</h2>";
echo "<p>tblapproval_flows: " . ($flows_exists ? "<span style='color: green;'>EXISTS</span>" : "<span style='color: red;'>MISSING</span>") . "</p>";
echo "<p>tblapproval_flow_steps: " . ($steps_exists ? "<span style='color: green;'>EXISTS</span>" : "<span style='color: red;'>MISSING</span>") . "</p>";
echo "<p>tbltask_approvals: " . ($approvals_exists ? "<span style='color: green;'>EXISTS</span>" : "<span style='color: red;'>MISSING</span>") . "</p>";
echo "<p>tbltask_approval_remark_history: " . ($history_exists ? "<span style='color: green;'>EXISTS</span>" : "<span style='color: red;'>MISSING</span>") . "</p>";
echo "<p>tblapproval_flows.status column: " . ($flows_has_status ? "<span style='color: green;'>EXISTS</span>" : "<span style='color: red;'>MISSING</span>") . "</p>";

if (!$flows_exists || !$steps_exists || !$approvals_exists || !$history_exists || !$flows_has_status) {
    echo "<h2>Creating Tables...</h2>";

    // Create approval flows table
    if (!$flows_exists) {
        $sql = "CREATE TABLE `$flows_table` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(255) NOT NULL,
            `description` text,
            `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1=Active, 0=Inactive',
            `created_by` int(11) NOT NULL,
            `created_at` datetime NOT NULL,
            `updated_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `created_by` (`created_by`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        execute_sql($sql);
    } else {
        // Table exists but check if status column exists
        if (!$flows_has_status) {
            $sql = "ALTER TABLE `$flows_table` ADD COLUMN `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1=Active, 0=Inactive' AFTER `description`;";
            execute_sql($sql);
        }
    }

    // Create approval flow steps table
    if (!$steps_exists) {
        $sql = "CREATE TABLE `$steps_table` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `approval_flow_id` int(11) NOT NULL,
            `staff_id` int(11) NOT NULL,
            `step_order` int(11) NOT NULL,
            `step_name` varchar(255) DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `approval_flow_id` (`approval_flow_id`),
            KEY `staff_id` (`staff_id`),
            CONSTRAINT `fk_approval_flow_steps_flow` FOREIGN KEY (`approval_flow_id`) REFERENCES `$flows_table` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_approval_flow_steps_staff` FOREIGN KEY (`staff_id`) REFERENCES `tblstaff` (`staffid`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        execute_sql($sql);
    }

    // Create task approvals table
    if (!$approvals_exists) {
        $sql = "CREATE TABLE `tbltask_approvals` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `task_id` int(11) NOT NULL,
            `staff_id` int(11) NOT NULL,
            `step_order` int(11) NOT NULL,
            `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
            `approved_at` datetime DEFAULT NULL,
            `rejected_at` datetime DEFAULT NULL,
            `comments` text,
            `created_at` datetime NOT NULL,
            `updated_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `task_id` (`task_id`),
            KEY `staff_id` (`staff_id`),
            KEY `step_order` (`step_order`),
            CONSTRAINT `fk_task_approvals_task` FOREIGN KEY (`task_id`) REFERENCES `tbltasks` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_task_approvals_staff` FOREIGN KEY (`staff_id`) REFERENCES `tblstaff` (`staffid`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        execute_sql($sql);
    }

    // Create task approval remark history table
    if (!$history_exists) {
        $sql = "CREATE TABLE `tbltask_approval_remark_history` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `task_id` int(11) NOT NULL,
            `task_approval_id` int(11) NOT NULL,
            `staff_id` int(11) NOT NULL,
            `action_type` enum('remark','approve','reject','revert') NOT NULL,
            `comments` text,
            `created_at` datetime NOT NULL,
            PRIMARY KEY (`id`),
            KEY `task_id` (`task_id`),
            KEY `task_approval_id` (`task_approval_id`),
            KEY `staff_id` (`staff_id`),
            CONSTRAINT `fk_task_approval_history_task` FOREIGN KEY (`task_id`) REFERENCES `tbltasks` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_task_approval_history_approval` FOREIGN KEY (`task_approval_id`) REFERENCES `$approvals_table` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_task_approval_history_staff` FOREIGN KEY (`staff_id`) REFERENCES `tblstaff` (`staffid`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        execute_sql($sql);
    }

    // Add permissions if not exist
    echo "<h2>Adding Permissions...</h2>";
    $permissions = [
        [1, 'approval_flow', 'view'],
        [1, 'approval_flow', 'create'],
        [1, 'approval_flow', 'edit'],
        [1, 'approval_flow', 'delete']
    ];

    foreach ($permissions as $perm) {
        $check_sql = "SELECT COUNT(*) as count FROM tblstaff_permissions WHERE staff_id = {$perm[0]} AND feature = '{$perm[1]}' AND capability = '{$perm[2]}'";
        $result = $conn->query($check_sql);
        $row = $result->fetch_assoc();

        if ($row['count'] == 0) {
            $insert_sql = "INSERT INTO tblstaff_permissions (staff_id, feature, capability) VALUES ({$perm[0]}, '{$perm[1]}', '{$perm[2]}')";
            execute_sql($insert_sql);
        } else {
            echo "<p style='color: blue;'>Permission already exists: {$perm[1]} - {$perm[2]}</p>";
        }
    }
} else {
    echo "<h2>All tables exist!</h2>";
}

$conn->close();
echo "<h2>Setup Complete</h2>";
echo "<p><a href='" . (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'http://localhost/admin/approval_flow') . "'>← Back to Approval Flow</a></p>";
?>
