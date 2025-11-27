-- Create table for storing external API configurations
CREATE TABLE IF NOT EXISTS `tbl_external_apis` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT 'Unique name for the external API',
  `api_url` text NOT NULL COMMENT 'Full URL of the external API endpoint',
  `request_method` varchar(10) NOT NULL DEFAULT 'GET' COMMENT 'HTTP method (GET, POST, PUT, DELETE)',
  `request_body` text DEFAULT NULL COMMENT 'JSON body for POST/PUT requests',
  `headers` text DEFAULT NULL COMMENT 'JSON format headers',
  `cron_schedule` varchar(100) NOT NULL COMMENT 'Cron expression for scheduling',
  `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Whether the external API is active',
  `created_at` datetime NOT NULL,
  `last_run` datetime DEFAULT NULL COMMENT 'Last execution time',
  `next_run` datetime DEFAULT NULL COMMENT 'Next scheduled execution time',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `is_active` (`is_active`),
  KEY `next_run` (`next_run`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Create table for storing external API execution logs
CREATE TABLE IF NOT EXISTS `tbl_external_api_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `external_api_id` int(11) DEFAULT NULL COMMENT 'Reference to tbl_external_apis.id',
  `status_code` int(11) DEFAULT NULL COMMENT 'HTTP status code from API response',
  `response_body` longtext DEFAULT NULL COMMENT 'Full response from the external API',
  `error_message` text DEFAULT NULL COMMENT 'Error message if the API call failed',
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `external_api_id` (`external_api_id`),
  KEY `status_code` (`status_code`),
  KEY `created_at` (`created_at`),
  FOREIGN KEY (`external_api_id`) REFERENCES `tbl_external_apis` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
