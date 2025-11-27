-- Add otp_used column to tbl_sms_password_reset_logs table
-- This column tracks whether an OTP code has been used or not for security purposes

USE appdb;

-- Add the otp_used column after otp_code
ALTER TABLE tbl_sms_password_reset_logs
ADD COLUMN otp_used TINYINT(1) NOT NULL DEFAULT 0 COMMENT '0 = not used, 1 = used' AFTER otp_code;

-- Add index for better query performance when checking OTP usage
ALTER TABLE tbl_sms_password_reset_logs
ADD INDEX idx_otp_used (otp_used);

-- Add composite index for OTP validation queries (otp_code + staffid + otp_used)
ALTER TABLE tbl_sms_password_reset_logs
ADD INDEX idx_otp_validation (otp_code, staffid, otp_used);

-- Update existing records to have otp_used = 0 (backward compatibility)
-- This is not strictly necessary since the column has DEFAULT 0, but it's explicit
UPDATE tbl_sms_password_reset_logs SET otp_used = 0 WHERE otp_used IS NULL;
