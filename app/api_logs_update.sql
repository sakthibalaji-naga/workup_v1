-- Add username column to tbl_api_logs table
-- This ensures usernames are preserved even if API users are deleted

USE appdb;

-- Add username column after api_key
ALTER TABLE tbl_api_logs
ADD COLUMN username varchar(100) DEFAULT '' AFTER api_key;

-- Update existing records to populate usernames from tbl_api_users
UPDATE tbl_api_logs
LEFT JOIN tbl_api_users ON tbl_api_logs.api_key = tbl_api_users.api_key
SET tbl_api_logs.username = COALESCE(tbl_api_users.username, 'N/A')
WHERE tbl_api_logs.username = '' OR tbl_api_logs.username IS NULL;

-- For future API calls, username will be stored directly
-- This preserves audit trail even if API keys/users are deleted

-- Optional: Add index for username for performance (but may not be needed for small tables)
-- ALTER TABLE tbl_api_logs ADD INDEX idx_username (username);

COMMIT;
