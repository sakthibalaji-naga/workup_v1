<?php
// Bootstrap CodeIgniter
require_once __DIR__ . '/index.php';

// Initialize database connection
$CI =& get_instance();

// Check if tbl_api_users table exists
if (!$CI->db->table_exists('tbl_api_users')) {
    echo "Creating tbl_api_users table...\n";

    $CI->db->query("CREATE TABLE `tbl_api_users` (
        `id` int NOT NULL AUTO_INCREMENT,
        `username` varchar(100) NOT NULL,
        `api_key` varchar(64) NOT NULL,
        `perm_get` tinyint(1) NOT NULL DEFAULT '0',
        `perm_post` tinyint(1) NOT NULL DEFAULT '0',
        `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `username` (`username`),
        UNIQUE KEY `api_key` (`api_key`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
}

// Check if tbl_api_logs table exists
if (!$CI->db->table_exists('tbl_api_logs')) {
    echo "Creating tbl_api_logs table...\n";

    $CI->db->query("CREATE TABLE `tbl_api_logs` (
        `id` int NOT NULL AUTO_INCREMENT,
        `api_key` varchar(64) DEFAULT NULL,
        `username` varchar(100) DEFAULT NULL,
        `endpoint` varchar(255) NOT NULL,
        `method` varchar(10) NOT NULL,
        `status_code` int NOT NULL,
        `request_body` text,
        `response_body` text,
        `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `api_key` (`api_key`),
        KEY `username` (`username`),
        KEY `endpoint` (`endpoint`),
        KEY `created_at` (`created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
}

// Create sample API user
$CI->db->where('username', 'test_user');
$existing_user = $CI->db->get('tbl_api_users')->row();

if (!$existing_user) {
    // Generate API key like the controller does
    $api_key = bin2hex(openssl_random_pseudo_bytes(16));

    $CI->db->insert('tbl_api_users', [
        'username' => 'test_user',
        'api_key' => $api_key,
        'perm_get' => 1,
        'perm_post' => 1,
        'created_at' => date('Y-m-d H:i:s')
    ]);

    echo "✅ Created API User:\n";
    echo "Username: test_user\n";
    echo "API Key: $api_key\n";
    echo "Permissions: GET=yes, POST=yes\n\n";

    echo "🔥 Use this API key in your curl command:\n";
    echo "curl -X POST \"http://localhost/api/v1/create_ticket\" ";
    echo "-H \"accept: application/json\" ";
    echo "-H \"Content-Type: multipart/form-data\" ";
    echo "-H \"X-API-Key: $api_key\" ";
    echo "-F \"subject=Test Ticket\" ";
    echo "-F \"message=This is a test ticket.\"";
} else {
    echo "✅ API User already exists:\n";
    echo "Username: " . $existing_user->username . "\n";
    echo "API Key: " . $existing_user->api_key . "\n";
    echo "Permissions: GET=" . ($existing_user->perm_get ? 'yes' : 'no') . ", POST=" . ($existing_user->perm_post ? 'yes' : 'no') . "\n\n";

    echo "🔥 Use this API key in your curl command:\n";
    echo "curl -X POST \"http://localhost/api/v1/create_ticket\" ";
    echo "-H \"accept: application/json\" ";
    echo "-H \"Content-Type: multipart/form-data\" ";
    echo "-H \"X-API-Key: " . $existing_user->api_key . "\" ";
    echo "-F \"subject=Test Ticket\" ";
    echo "-F \"message=This is a test ticket.\"";
}

echo "\n\n📝 Note: Remember to include the X-API-Key header in all your API requests!\n";
