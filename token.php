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

// Connect to SQLite database
$db = new SQLite3($constants['Bloggable Database']);

// Parse command-line arguments
$args = $argv;
array_shift($args); // Remove the script name

// Determine the action description
$actionDescription = count($args) > 0 && $args[0] === 'revoke' ? 'Revoke Token' : 'View Tokens';

// Log the action
$sql = "INSERT INTO ACTION (action_priority, action_source, app_id, action_ip_address, action_description) VALUES (?, ?, ?, ?, ?)";
$stmt = $db->prepare($sql);
$stmt->bindValue(1, 1, SQLITE3_INTEGER);
$stmt->bindValue(2, 'admin', SQLITE3_TEXT);
$stmt->bindValue(3, 'token.php', SQLITE3_TEXT);
$stmt->bindValue(4, 'localhost: console', SQLITE3_TEXT);
$stmt->bindValue(5, $actionDescription, SQLITE3_TEXT);
$result = $stmt->execute();

// Check if revoke parameter is provided
if (count($args) > 0 && $args[0] === 'revoke') {
    $accountId = $args[1] ?? null;

    if ($accountId) {
        // Revoke the token for the provided account_id
        $sql = "DELETE FROM TOKEN WHERE issued_for = ?";
        $stmt = $db->prepare($sql);
        $stmt->bindValue(1, $accountId, SQLITE3_TEXT);
        $result = $stmt->execute();

        echo "Revoked token for account_id: $accountId\n";
    } else {
        echo "Please provide an account_id to revoke the token.\n";
    }
} else {
    // Prepare and execute SQL statement to select from TOKEN table
    $sql = "SELECT token, expires_at, issued_at, issued_by, issued_for FROM TOKEN";
    $result = $db->query($sql);

    // Check if there are any rows
    if ($result === false) {
        // SQL error occurred
        echo "Error executing SQL query: " . $db->lastErrorMsg() . PHP_EOL;
    } else {
        // Get column names
        $columns = ['token', 'expires_at', 'issued_at', 'issued_by', 'issued_for'];

        // Print header
        echo str_pad($columns[0], 20) . str_pad($columns[1], 20) . str_pad($columns[2], 20) . str_pad($columns[3], 20) . str_pad($columns[4], 20) . PHP_EOL;

        // Print divider
        echo str_repeat('-', 100) . PHP_EOL;

        // Fetch and print the data
        $rowCount = 0;
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            echo str_pad($row['token'], 20) . str_pad($row['expires_at'], 20) . str_pad($row['issued_at'], 20) . str_pad($row['issued_by'], 20) . str_pad($row['issued_for'], 20) . PHP_EOL;
            $rowCount++;
        }

        // If no rows were found, print a message
        if ($rowCount === 0) {
            echo "No token records found." . PHP_EOL;
        }
    }
}

// Close database connection
$db->close();

?>  
  
