-- Fix tbl_api_logs table by adding missing columns
-- This script adds the request_body and response_body columns that are required by the API controller

USE appdb;

-- Add request_body column
ALTER TABLE tbl_api_logs
ADD COLUMN request_body TEXT DEFAULT '' AFTER status_code;

-- Add response_body column
ALTER TABLE tbl_api_logs
ADD COLUMN response_body TEXT DEFAULT '' AFTER request_body;

-- Verify the table structure
DESCRIBE tbl_api_logs;

COMMIT;
