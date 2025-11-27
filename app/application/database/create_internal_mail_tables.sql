-- Internal Mail System Tables

-- Main mail messages table
CREATE TABLE IF NOT EXISTS `tblinternal_mail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `from_staff_id` int(11) NOT NULL,
  `date_sent` datetime NOT NULL,
  `is_draft` tinyint(1) NOT NULL DEFAULT 0,
  `priority` enum('low','normal','high') DEFAULT 'normal',
  `has_attachments` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `from_staff_id` (`from_staff_id`),
  KEY `date_sent` (`date_sent`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Mail recipients table (TO, CC, BCC)
CREATE TABLE IF NOT EXISTS `tblinternal_mail_recipients` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mail_id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `recipient_type` enum('to','cc','bcc') NOT NULL DEFAULT 'to',
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `read_date` datetime NULL DEFAULT NULL,
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `deleted_date` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mail_id` (`mail_id`),
  KEY `staff_id` (`staff_id`),
  KEY `is_read` (`is_read`),
  KEY `is_deleted` (`is_deleted`),
  CONSTRAINT `fk_internal_mail_recipients_mail` FOREIGN KEY (`mail_id`) REFERENCES `tblinternal_mail` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Mail attachments table
CREATE TABLE IF NOT EXISTS `tblinternal_mail_attachments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mail_id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `original_file_name` varchar(255) NOT NULL,
  `file_type` varchar(100) NULL,
  `file_size` int(11) NULL,
  `date_added` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `mail_id` (`mail_id`),
  CONSTRAINT `fk_internal_mail_attachments_mail` FOREIGN KEY (`mail_id`) REFERENCES `tblinternal_mail` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Mail tracking/status table
CREATE TABLE IF NOT EXISTS `tblinternal_mail_tracking` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mail_id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `action` enum('sent','read','deleted','replied','forwarded') NOT NULL,
  `action_date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `mail_id` (`mail_id`),
  KEY `staff_id` (`staff_id`),
  CONSTRAINT `fk_internal_mail_tracking_mail` FOREIGN KEY (`mail_id`) REFERENCES `tblinternal_mail` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
