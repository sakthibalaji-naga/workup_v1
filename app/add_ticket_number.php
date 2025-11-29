<?php
/**
 * Script to add ticket_number column to tbltickets table and populate existing tickets
 */

// Hardcoded DB connection for install
$host = 'db';
$user = 'root';
$pass = 'root';
$dbname = 'appdb';
$prefix = 'tbl';

try {
    $pdo = new PDO("mysql:host={$host};dbname={$dbname}", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Add the column if it doesn't exist
    $check_column = $pdo->query("SHOW COLUMNS FROM `{$prefix}tickets` LIKE 'ticket_number'");
    if ($check_column->rowCount() == 0) {
        $pdo->query("ALTER TABLE `{$prefix}tickets` ADD `ticket_number` VARCHAR(7) NULL AFTER `date`");
        echo "Column 'ticket_number' added successfully.\n";
    } else {
        echo "Column 'ticket_number' already exists.\n";
    }

    // Populate existing tickets
    // Get tickets grouped by year, ordered by date
    $stmt = $pdo->query("SELECT `ticketid`, `date`, YEAR(`date`) AS `year` FROM `{$prefix}tickets` ORDER BY `date`");

    $sequences = [];
    $updates = [];

    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $year = (int)$row['year'];
        $ticketid = (int)$row['ticketid'];

        if (!isset($sequences[$year])) {
            $sequences[$year] = 1;
        } else {
            $sequences[$year]++;
        }

        $sequence = $sequences[$year];
        $yy = str_pad($year % 100, 2, '0', STR_PAD_LEFT);
        $seq = str_pad($sequence, 5, '0', STR_PAD_LEFT);
        $ticket_number = $yy . $seq;

        $updates[] = "UPDATE `{$prefix}tickets` SET `ticket_number` = '{$ticket_number}' WHERE `ticketid` = {$ticketid}";
    }

    // Execute updates
    foreach ($updates as $update) {
        $pdo->exec($update);
    }

    echo "Existing tickets populated with ticket_number.\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
