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

// Log the action
$sql = "INSERT INTO ACTION (action_priority, action_source, app_id, action_ip_address, action_description) VALUES (?, ?, ?, ?, ?)";
$stmt = $db->prepare($sql);
$stmt->bindValue(1, 1, SQLITE3_INTEGER);
$stmt->bindValue(2, 'admin', SQLITE3_TEXT);
$stmt->bindValue(3, 'action.php', SQLITE3_TEXT);
$stmt->bindValue(4, 'localhost: console', SQLITE3_TEXT);
$stmt->bindValue(5, 'View Actions', SQLITE3_TEXT);
$result = $stmt->execute();

// Prepare and execute SQL statement to select from ACTION table
$sql = "SELECT action_timestamp, action_priority, action_source, account_id, author_id, weblog_id, blog_id, app_id, action_ip_address, action_description FROM ACTION ORDER BY action_timestamp DESC LIMIT 50";
$result = $db->query($sql);

// Check if there are any rows
if ($result === false) {
    // SQL error occurred
    echo "Error executing SQL query: " . $db->lastErrorMsg() . PHP_EOL;
} else {
    // Get column names
    $columns = ['action_timestamp', 'action_priority', 'action_source', 'account_id', 'author_id', 'weblog_id', 'blog_id', 'app_id', 'action_ip_address', 'action_description'];

    // Print header
    foreach ($columns as $column) {
        echo str_pad($column, 20);
    }
    echo PHP_EOL;

    // Print divider
    echo str_repeat('-', 20 * count($columns)) . PHP_EOL;

    // Fetch and print the data
    $rowCount = 0;
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        foreach ($columns as $column) {
            echo str_pad($row[$column], 20);
        }
        echo PHP_EOL;
        $rowCount++;
    }

    // If no rows were found, print a message
    if ($rowCount === 0) {
        echo "No action records found." . PHP_EOL;
    }
}

// Close database connection
$db->close();

?>
