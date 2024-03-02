#!/usr/bin/env php
<?php

// setpasswd.php
//
// This is primarily just used to set the password for a Bloggability account initially,
// like when first getting started with the project. Ultimately, most of the time the
// appropriate REST API endpoint would be used, but this is a way to more easily see
// how passwords are implemented here. 
// 
// The basic idea is that a hash of the password is stored using the SHA-256 hashing
// algorithm. For a little extra fun, a secret labelled "ACCOUNT" is pulled from the
// keystore, and then combined with the account_id for the record in question, along
// with whatever password is being set, to generate the hash. Later, when checking
// for the correct password as part of the Login endpoint, the same steps are used.


// Most actions are logged using a timer to monitor performance
$startTime = microtime(true);

// Check if running from the command line
if (php_sapi_name() !== 'cli') {
    print "Error: This program can only be executed from the command line.".PHP_EOL;
    exit(99);
}

// Parse command-line arguments, check if account_id and password are provided
$args = $argv;
array_shift($args); 
if (count($args) !== 2) {
    print "Usage: setpasswd.php <account_id> <password>".PHP_EOL;
    exit(1);
}


// This is a set of utility functions to help with our multi-vendor database situation
require_once 'dbsupport.php';


// Get configuration settings from JSON file
// NOTE: Everything we need shoudl be derived from these values.
$constantsFile = 'bloggability.json';
$constantsData = file_get_contents($constantsFile);
$constants = json_decode($constantsData, true);


// Get secret keys from special keystore location
$secrets = getSecrets($constants);
if (!$secrets) {
    print "Error: Keystore not available.".PHP_EOL;
    exit(5);
}


// Establish connection to the database
[$db, $dbName, $dbEngine] = getConnection($constants);
if (!$db || !$dbName || !$dbEngine) {
    print "Error: Database connection not established.".PHP_EOL;
    exit(2);
}


// Everything we need to proceed
$accountId = $args[0];
$password = $args[1];
$runningTime = round(microtime(true) - $startTime, 3) * 1000;


// Find the "ACCOUNT" key from the keystore
$accountKey = null;
foreach ($secrets['keys'] as $keyEntry) {
    if ($keyEntry['Key'] === 'ACCOUNT') {
        $accountKey = $keyEntry['Secret'];
        break;
    }
}


// If the account key is not found, exit with an error
if ($accountKey === null) {
    print "Error: ACCOUNT key not found in the keystore.".PHP_EOL;
    exit(7);
}


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
    'param3' => $accountId,
    'param4' => 'setpasswd.php',
    'param5' => 'localhost: console',
    'param6' => 'Update Pasword',
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


// Hash the password using the ACCOUNT key and account_id
$hash = hash('sha256', $accountKey . $accountId . $password);


// Update the password_hash in the ACCOUNT table
$sql = <<<QUERY
    UPDATE ACCOUNT 
	SET account_password_hash = ?, 
	    account_updated_by = ?, 
	    account_updated_at = ? 
        WHERE account_id = ?
QUERY;
$params = [
    'dtypes' => 'ssss',
    'param1' => $hash,
    'param2' => 'admin', 
    'param3' => gmdate('Y-m-d H:i:s'),
    'param4' => $accountId
];
[$results, $affected, $errors] = queryExecute($dbEngine, $db, $sql, $params);


// Process the results
if (!$results) {
    print "Error executing statement: ".$errors.PHP_EOL;
    exit(4);
} else {
    print "ACCOUNT Rows Affected: ".$affected.PHP_EOL;
}


// Close database connection
closeConnection($dbEngine, $db);


// All done
print "Password for $accountId was changed successfully.".PHP_EOL;


?>
