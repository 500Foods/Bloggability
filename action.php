#!/usr/bin/env php
<?php

// action.php
//
// This is used to provide quick access to view the contents of the ACTION log
// from the Bloggability database. The only optional parameter is for the
// account_id to filter the ACTION log by. This is also a simple enough app
// to serve as a test for the Bloggability multi-vendor database mechanism.


// Most actions are logged using a timer to monitor performance
$startTime = microtime(true);


// Check if running from the command line
if (php_sapi_name() !== 'cli') {
    echo "This program can only be executed from the command line.";
    return;
}

// This is a set of utility functions to help with our multi-vendor database situation
require_once 'dbsupport.php';


// Get configuration settings from JSON file
// NOTE: Everything we need shoudl be derived from these values.
$constantsFile = 'bloggability.json';
if (!file_exists($constantsFile)) {
    print "Configuration file not found: $constantsFile".PHP_EOL;
    exit(1);
}
$constantsData = file_get_contents($constantsFile);
$constants = json_decode($constantsData, true);


// Establish connection to the database
[$db, $dbName, $dbEngine] = getConnection($constants);
if (!$db || !$dbName || !$dbEngine) {
    print "Error: Database connection not established.".PHP_EOL;
    exit(2);
}


// Parse command-line arguments
$args = $argv;
array_shift($args);
$account_id = $args[0] ?? '%';
$runningTime = round(microtime(true) - $startTime, 3) * 1000;


// Log the action
$sql = <<<QUERY
    INSERT INTO ACTION
        (action_priority, action_source, action_account_id, action_app_id, action_ip_address, action_description, action_execution_time)
    VALUES
        (?, ?, ?, ?, ?, ?, ?)
QUERY;
$params = [
    'dtypes' => 'issssss',
    'param1' => 1,
    'param2' => 'admin',
    'param3' => 'admin',
    'param4' => 'action.php',
    'param5' => 'localhost: console',
    'param6' => 'View Actions ['.$account_id.']',
    'param7' => $runningTime
];
[$results, $affected, $errors] = queryExecute($dbEngine, $db, $sql, $params);


// Process the results
if (!$results) {
    print "Error executing statement: ".$errors.PHP_EOL;
    exit(4);
} else {
    print "ACTION Rows Affected: ".$affected.PHP_EOL;
}

// Retrieve ACTION log records
$sql = <<<QUERY
    SELECT
        action_timestamp, action_priority, action_source, action_account_id, action_author_id, action_weblog_id, action_blog_id, action_app_id, action_ip_address, action_description, action_execution_time
    FROM
        ACTION
    WHERE
        action_account_id LIKE ?
    ORDER BY
        action_timestamp DESC
    LIMIT 50
QUERY;
$params = [
    'dtypes' => 's',
    'param1' => $account_id.'%',
];
[$results, $affected, $errors] = queryFetch($dbEngine, $db, $sql, $params);


// Process results
if ($errors !== 'Success') {
    print "Error executing statement: ".$errors.PHP_EOL;
    exit(4);
 } else {
     print "ACTION Rows Retrieved: ".$affected.PHP_EOL;
}

// Get column names
$columns = [
    'timestamp            ',
    'priority  ',
    'source              ',
    'account_id          ',
    'author_id           ',
    'weblog_id           ',
    'blog_id             ',
    'app_id              ',
    'ip_address          ',
    'execution_time      ',
    'description         '
];


// Print header
foreach ($columns as $column) {
    print $column;
}
print PHP_EOL;
print str_repeat('-', 21 * count($columns)).PHP_EOL;


// Fetch and print the data
$rowCount = 0;
while ($rowCount < count($results)) {
    $row = $results[$rowCount];
    print str_pad(substr($row['action_timestamp'      ],0,19), 21).
          str_pad(substr($row['action_priority'       ],0,10), 10).
          str_pad(substr($row['action_source'         ],0,19), 20).
          str_pad(substr($row['action_account_id'     ],0,19), 20).
          str_pad(substr($row['action_author_id'      ],0,19), 20).
          str_pad(substr($row['action_weblog_id'      ],0,19), 20).
          str_pad(substr($row['action_blog_id'        ],0,19), 20).
          str_pad(substr($row['action_app_id'         ],0,19), 20).
          str_pad(substr($row['action_ip_address'     ],0,19), 20).
          str_pad(substr($row['action_execution_time' ],0,19), 20).
          str_pad(substr($row['action_description'    ],0,19), 20).PHP_EOL;
    $rowCount++;
}

// If no rows were found, print a message
if ($rowCount === 0) {
   print "No ACTION records found." . PHP_EOL;
}


// Close database connection
closeConnection($dbEngine, $db);


?>
