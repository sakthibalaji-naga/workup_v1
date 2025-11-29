-- Update ticket ID 5 to have ticket_number 2500005
UPDATE tbltickets SET ticket_number = '2500005' WHERE ticketid = 5;
-- Check all tickets without ticket_number and set based on sequence
-- This will fill missing values if any
