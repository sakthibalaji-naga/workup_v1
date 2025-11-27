-- SMS Logs Table
-- This table stores logs of all SMS trigger attempts

CREATE TABLE IF NOT EXISTS `tbl_sms_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `trigger_type` varchar(50) NOT NULL COMMENT 'SMS trigger type (invoice_overdue_notice, ticket_created, etc.)',
  `phone_number` varchar(50) NOT NULL COMMENT 'Phone number SMS was sent to',
  `sms_gateway` varchar(100) NOT NULL COMMENT 'SMS gateway used (twilio, msg91, clickatell)',
  `message_content` text NOT NULL COMMENT 'Full SMS message content',
  `merge_fields` text NULL COMMENT 'JSON encoded merge fields used',
  `status` enum('sent','failed','queued','cancelled') NOT NULL DEFAULT 'queued',
  `sent_at` datetime DEFAULT NULL,
  `queue_at` datetime DEFAULT NULL,
  `error_message` text NULL COMMENT 'Error details if SMS failed',
  `ip_address` varchar(50) DEFAULT NULL COMMENT 'IP address of requesting client',
  `user_agent` text DEFAULT NULL COMMENT 'Browser/user agent info',
  `staff_id` int(11) DEFAULT NULL COMMENT 'Staff member ID if applicable',
  `client_id` int(11) DEFAULT NULL COMMENT 'Client ID if applicable',
  `contact_id` int(11) DEFAULT NULL COMMENT 'Contact ID if applicable',
  `related_record_id` int(11) DEFAULT NULL COMMENT 'ID of related record (ticket_id, invoice_id, etc.)',
  `related_record_type` varchar(50) DEFAULT NULL COMMENT 'Type of related record (ticket, invoice, contract, etc.)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `trigger_type` (`trigger_type`),
  KEY `phone_number` (`phone_number`),
  KEY `status` (`status`),
  KEY `staff_id` (`staff_id`),
  KEY `client_id` (`client_id`),
  KEY `created_at` (`created_at`),
  KEY `staff_id_phone` (`staff_id`, `phone_number`),
  KEY `related_record` (`related_record_id`, `related_record_type`),
  CONSTRAINT `fk_sms_logs_staff` FOREIGN KEY (`staff_id`) REFERENCES `tblstaff` (`staffid`) ON DELETE SET NULL,
  CONSTRAINT `fk_sms_logs_client` FOREIGN KEY (`client_id`) REFERENCES `tblclients` (`userid`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Sample query to get recent SMS logs:
-- SELECT * FROM tbl_sms_logs WHERE staff_id = ? OR client_id = ? ORDER BY created_at DESC LIMIT 10;
