-- API Response Field Mappings Table
-- This table stores the parsed API response fields for flow builder conditions

CREATE TABLE IF NOT EXISTS `tbl_api_response_mappings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `external_api_id` int(11) NOT NULL,
  `field_path` varchar(500) NOT NULL COMMENT 'JSON path to the field (e.g., "Code", "Employees[0].EmployeeName")',
  `field_name` varchar(255) NOT NULL COMMENT 'Display name for the field',
  `field_type` varchar(50) NOT NULL DEFAULT 'string' COMMENT 'Data type: string, number, boolean, array, object',
  `is_array_element` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Whether this field represents an array element',
  `array_index` int(11) DEFAULT NULL COMMENT 'Index position if it\'s an array element',
  `parent_path` varchar(500) DEFAULT NULL COMMENT 'Parent path for nested fields',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `external_api_id` (`external_api_id`),
  KEY `field_path` (`field_path`(255)),
  KEY `field_name` (`field_name`),
  UNIQUE KEY `unique_mapping` (`external_api_id`, `field_path`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Add foreign key constraint
-- ALTER TABLE `tbl_api_response_mappings` ADD CONSTRAINT `fk_api_response_mappings_external_api_id` FOREIGN KEY (`external_api_id`) REFERENCES `tbl_external_apis` (`id`) ON DELETE CASCADE;
