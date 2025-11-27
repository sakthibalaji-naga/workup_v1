-- Flow Builder Tables
-- Run this file to create the necessary tables for the Flow Builder functionality

CREATE TABLE IF NOT EXISTS `tbl_flows` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text,
  `success_log_message` text,
  `failure_log_message` text,
  `flow_data` longtext NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_by` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE IF NOT EXISTS `tbl_flow_execution_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `flow_id` int(11) NOT NULL,
  `status` varchar(50) NOT NULL,
  `execution_time` float NOT NULL,
  `result` longtext,
  `log_message` text,
  `executed_at` datetime NOT NULL,
  `executed_by` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `flow_id` (`flow_id`),
  KEY `status` (`status`),
  KEY `executed_at` (`executed_at`),
  KEY `executed_by` (`executed_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Add foreign key constraints if needed
-- ALTER TABLE `tbl_flows` ADD CONSTRAINT `fk_flows_created_by` FOREIGN KEY (`created_by`) REFERENCES `tblstaff` (`staffid`) ON DELETE CASCADE;
-- ALTER TABLE `tbl_flow_execution_logs` ADD CONSTRAINT `fk_execution_logs_flow_id` FOREIGN KEY (`flow_id`) REFERENCES `tbl_flows` (`id`) ON DELETE CASCADE;
-- ALTER TABLE `tbl_flow_execution_logs` ADD CONSTRAINT `fk_execution_logs_executed_by` FOREIGN KEY (`executed_by`) REFERENCES `tblstaff` (`staffid`) ON DELETE CASCADE;
