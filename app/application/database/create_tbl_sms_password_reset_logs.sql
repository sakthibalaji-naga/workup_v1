-- SMS Password Reset Logs Table
-- This table stores logs of SMS password reset attempts

CREATE TABLE IF NOT EXISTS `tbl_sms_password_reset_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `staffid` int(11) NOT NULL COMMENT 'Staff member ID who requested password reset',
  `phone_number` varchar(50) NOT NULL COMMENT 'Phone number SMS was sent to',
  `otp_code` varchar(10) NOT NULL COMMENT '5-digit OTP code sent',
  `sms_gateway` varchar(100) NOT NULL COMMENT 'SMS gateway used (twilio, msg91, clickatell)',
  `trigger_type` varchar(50) NOT NULL DEFAULT 'staff_password_reset',
  `message_content` text NOT NULL COMMENT 'Full SMS message content',
  `status` enum('sent','failed','queued') NOT NULL DEFAULT 'queued',
  `sent_at` datetime DEFAULT NULL,
  `error_message` text NULL COMMENT 'Error details if SMS failed',
  `ip_address` varchar(50) DEFAULT NULL COMMENT 'IP address of the request',
  `user_agent` text DEFAULT NULL COMMENT 'Browser/user agent info',
  `attempts_count` int(11) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `staffid` (`staffid`),
  KEY `phone_number` (`phone_number`),
  KEY `status` (`status`),
  KEY `created_at` (`created_at`),
  KEY `staffid_phone` (`staffid`, `phone_number`),
  CONSTRAINT `fk_sms_password_reset_logs_staffid` FOREIGN KEY (`staffid`) REFERENCES `tblstaff` (`staffid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Sample query to get recent password reset attempts:
-- SELECT * FROM tbl_sms_password_reset_logs WHERE staffid = ? ORDER BY created_at DESC LIMIT 10;
