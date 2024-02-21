#!/usr/bin/env php
<?php

$constantsFile = 'bloggable.json';
$constantsData = file_get_contents($constantsFile);
$constants = json_decode($constantsData, true);

// Check if running from the command line
if (php_sapi_name() !== 'cli') {
    echo "This program can only be executed from the command line.";
    return;
}

// Check if SQLite3 extension is loaded
if (!extension_loaded('sqlite3')) {
    echo "SQLite3 extension is not loaded. Please enable it in your PHP configuration.";
    return;
}

// Check if the JWT Secret is at least 32 characters long
$jwtSecret = $constants['Bloggable JWT Secret'];
if (strlen($jwtSecret) < 32) {
    // Log the action
    $db = new SQLite3($constants['Bloggable Database']);
    $sql = "INSERT INTO ACTION (action_priority, action_source, app_id, account_id, action_ip_address, action_description) VALUES (?, ?, ?, ?, ?)";
    $stmt = $db->prepare($sql);
    $stmt->bindValue(1, 4, SQLITE3_INTEGER); // Exception priority
    $stmt->bindValue(2, 'admin', SQLITE3_TEXT);
    $stmt->bindValue(3, 'setpasswd.php', SQLITE3_TEXT);
    $stmt->bindValue(4, 'localhost', SQLITE3_TEXT);
    $stmt->bindValue(5, 'JWT Secret is too short', SQLITE3_TEXT);
    $result = $stmt->execute();
    $db->close();

    echo "Error: JWT Secret must be at least 32 characters long." . PHP_EOL;
    return;
}

// Parse command-line arguments
$args = $argv;
array_shift($args); // Remove the script name

// Check if account_id and password are provided
if (count($args) !== 2) {
    echo "Usage: setpasswd.php <account_id> <password>" . PHP_EOL;
    return;
}

$accountId = $args[0];
$password = $args[1];

// Connect to SQLite database
$db = new SQLite3($constants['Bloggable Database']);

// Log the action
$sql = "INSERT INTO ACTION (action_priority, action_source, account_id, app_id, action_ip_address, action_description) VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $db->prepare($sql);
$stmt->bindValue(1, 1, SQLITE3_INTEGER); // Info priority
$stmt->bindValue(2, 'admin', SQLITE3_TEXT);
$stmt->bindValue(3, $accountId, SQLITE3_TEXT); // Default account_id
$stmt->bindValue(4, 'setpasswd.php', SQLITE3_TEXT); // Use script name as app_id
$stmt->bindValue(5, 'localhost: console', SQLITE3_TEXT);
$stmt->bindValue(6, 'Update Password', SQLITE3_TEXT);
$result = $stmt->execute();

// Hash the password using the JWT Secret and account_id
$hash = hash('sha256', $jwtSecret . $accountId . $password);

// Update the password_hash in the ACCOUNT table
$sql = "UPDATE ACCOUNT SET account_password_hash = ? WHERE account_id = ?";
$stmt = $db->prepare($sql);
$stmt->bindValue(1, $hash, SQLITE3_TEXT);
$stmt->bindValue(2, $accountId, SQLITE3_TEXT);
$result = $stmt->execute();

if ($result) {
    echo "Password updated successfully for account_id: $accountId" . PHP_EOL;
} else {
    echo "Error updating password for account_id: $accountId" . PHP_EOL;
}

// Close database connection
$db->close();

?>
