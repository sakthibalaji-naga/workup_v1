-- Database Migration for Internal Mail System
-- Run this script to update the database schema

SET FOREIGN_KEY_CHECKS=0;

-- 1. Create Threads Table
CREATE TABLE IF NOT EXISTS `tblinternal_mail_threads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(32) DEFAULT NULL,
  `subject` varchar(191) NOT NULL,
  `created_at` datetime NOT NULL,
)
-- Database Migration for Internal Mail System
-- Run this script to update the database schema

SET FOREIGN_KEY_CHECKS=0;

-- 1. Create Threads Table
CREATE TABLE IF NOT EXISTS `tblinternal_mail_threads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(32) DEFAULT NULL,
  `subject` varchar(191) NOT NULL,
  `created_at` datetime NOT NULL,
  `last_message_at` datetime NOT NULL,
  `original_mail_id` int(11) DEFAULT NULL COMMENT 'Temporary column for migration',
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Safely add columns if table exists but columns don't
DROP PROCEDURE IF EXISTS `UpgradeThreadsTable`;
DELIMITER //
CREATE PROCEDURE `UpgradeThreadsTable`()
BEGIN
    IF NOT EXISTS (SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tblinternal_mail_threads' AND COLUMN_NAME = 'original_mail_id') THEN
        ALTER TABLE `tblinternal_mail_threads` ADD COLUMN `original_mail_id` int(11) DEFAULT NULL COMMENT 'Temporary column for migration';
    END IF;
    
    IF NOT EXISTS (SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tblinternal_mail_threads' AND COLUMN_NAME = 'token') THEN
        ALTER TABLE `tblinternal_mail_threads` ADD COLUMN `token` varchar(32) DEFAULT NULL;
        ALTER TABLE `tblinternal_mail_threads` ADD UNIQUE KEY `token` (`token`);
    END IF;
END //
DELIMITER ;
CALL `UpgradeThreadsTable`();
DROP PROCEDURE `UpgradeThreadsTable`;

-- 2. Create Folders Table (for Custom Folders)
CREATE TABLE IF NOT EXISTS `tblinternal_mail_folders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 3. Create Message Folders Table (User's view of messages)
CREATE TABLE IF NOT EXISTS `tblinternal_mail_message_folders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `message_id` int(11) NOT NULL,
  `folder_id` int(11) DEFAULT NULL COMMENT 'For custom folders',
  `system_folder` varchar(20) DEFAULT NULL COMMENT 'inbox, sent, drafts, trash, archive',
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `is_starred` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `message_id` (`message_id`),
  KEY `system_folder` (`system_folder`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 4. Create Audit Logs Table
CREATE TABLE IF NOT EXISTS `tblinternal_mail_audit_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `action` varchar(50) NOT NULL,
  `entity_type` varchar(50) NOT NULL,
  `entity_id` int(11) NOT NULL,
  `metadata_json` text,
  `timestamp` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 5. Alter Existing Mail Table
-- Check if columns exist before adding (using a procedure or just try-catch logic, but for simple script we assume they don't exist)
-- Note: MySQL doesn't support IF NOT EXISTS for columns in ALTER TABLE directly in all versions.
-- We will run these commands. If they fail, it might be because they already exist.

-- 5. Alter Existing Mail Table
-- Use a stored procedure to safely add columns if they don't exist
DROP PROCEDURE IF EXISTS `UpgradeInternalMailSchema`;
DELIMITER //
CREATE PROCEDURE `UpgradeInternalMailSchema`()
BEGIN
    -- Add thread_id
    IF NOT EXISTS (SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tblinternal_mail' AND COLUMN_NAME = 'thread_id') THEN
        ALTER TABLE `tblinternal_mail` ADD COLUMN `thread_id` int(11) DEFAULT NULL AFTER `id`;
        ALTER TABLE `tblinternal_mail` ADD KEY `thread_id` (`thread_id`);
    END IF;

    -- Add body_text
    IF NOT EXISTS (SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tblinternal_mail' AND COLUMN_NAME = 'body_text') THEN
        ALTER TABLE `tblinternal_mail` ADD COLUMN `body_text` text AFTER `message`;
    END IF;
END //
DELIMITER ;

CALL `UpgradeInternalMailSchema`();
DROP PROCEDURE `UpgradeInternalMailSchema`;

-- 6. Data Migration

-- 6.1 Migrate Threads
-- Create a thread for each existing message that hasn't been migrated yet
INSERT INTO `tblinternal_mail_threads` (subject, created_at, last_message_at, original_mail_id)
SELECT subject, date_sent, date_sent, id 
FROM `tblinternal_mail` 
WHERE thread_id IS NULL;

-- 6.2 Link Messages to Threads
UPDATE `tblinternal_mail` m
JOIN `tblinternal_mail_threads` t ON m.id = t.original_mail_id
SET m.thread_id = t.id
WHERE m.thread_id IS NULL;

-- Drop temporary column
ALTER TABLE `tblinternal_mail_threads` DROP COLUMN `original_mail_id`;

-- Populate tokens for existing threads (if null)
UPDATE `tblinternal_mail_threads` SET `token` = MD5(CONCAT(id, NOW(), RAND())) WHERE `token` IS NULL;

-- 6.3 Populate Inbox (from Recipients)
-- Only insert if not exists
INSERT INTO `tblinternal_mail_message_folders` (user_id, message_id, system_folder, is_read, created_at)
SELECT r.staff_id, r.mail_id, 'inbox', r.is_read, CURRENT_TIMESTAMP
FROM `tblinternal_mail_recipients` r
LEFT JOIN `tblinternal_mail_message_folders` mf ON mf.message_id = r.mail_id AND mf.user_id = r.staff_id
WHERE r.recipient_type IN ('to', 'cc', 'bcc') AND mf.id IS NULL;

-- 6.4 Populate Sent Items
INSERT INTO `tblinternal_mail_message_folders` (user_id, message_id, system_folder, is_read, created_at)
SELECT m.from_staff_id, m.id, 'sent', 1, m.date_sent
FROM `tblinternal_mail` m
LEFT JOIN `tblinternal_mail_message_folders` mf ON mf.message_id = m.id AND mf.user_id = m.from_staff_id
WHERE m.is_draft = 0 AND mf.id IS NULL;

-- 6.5 Populate Drafts
INSERT INTO `tblinternal_mail_message_folders` (user_id, message_id, system_folder, is_read, created_at)
SELECT m.from_staff_id, m.id, 'drafts', 1, m.date_sent
FROM `tblinternal_mail` m
LEFT JOIN `tblinternal_mail_message_folders` mf ON mf.message_id = m.id AND mf.user_id = m.from_staff_id
WHERE m.is_draft = 1 AND mf.id IS NULL;

SET FOREIGN_KEY_CHECKS=1;
