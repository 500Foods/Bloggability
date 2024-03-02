#!/usr/bin/env php
<?php

// addkey.php
//
// This is used to manage the Keystore, an encrypted JSON file
// that stores daily JWT secret keys as well as other secrets
// used by the Bloggability project.


// Check if the script is running from the command line
if (php_sapi_name() !== 'cli') {
    echo "This script can only be run from the command line.\n";
    exit(1);
}


// Get configuration settings from JSON file
// NOTE: Everything we need shoudl be derived from these values.
$constantsFile = 'bloggability.json';
if (!file_exists($constantsFile)) {
    print "Configuration file not found: $constantsFile".PHP_EOL;
    exit(1);
}
$constantsData = file_get_contents($constantsFile);
$constants = json_decode($constantsData, true);


// This is a set of utility functions to help with our multi-vendor database situation
require_once 'dbsupport.php';


// Get secret keys from special keystore location
$secrets = getSecrets($constants);
if (!$secrets) {
    print "Error: Keystore not available.".PHP_EOL;
    exit(5);
}
$keystoreFilename = $constants['Bloggability Keystore'] ?? null;
$encryptionKey = $constants['Bloggability Keystore Key'] ?? null;
$logFile = $constants['Bloggability Keystore Log'] ?? null;


// Make a note of who is initiating this request
$user = get_current_user();


// Handle the "LIST" parameter
if (isset($argv[1]) && strtoupper($argv[1]) === 'LIST') {
    print json_encode($secrets, JSON_PRETTY_PRINT).PHP_EOL;

    // Log the "LIST" action
    $logEntry = sprintf("[%s] %s: List command executed.", gmdate('Y-m-d H:i:s'), $user);
    file_put_contents($logFile, $logEntry . "\n", FILE_APPEND);

    exit(0);
}


// Get the current UTC date for the key value
$currentUtcDate = gmdate('Y-m-d');
$currentUtcTime = gmdate('Y-m-d H:i:s');


// Generate a new secret key
$newSecret = generateRandomSecret(40, 30, 5, 5);


// Get the key value (current date or from the provided parameter)
$keyValue = isset($argv[1]) ? $argv[1] : $currentUtcDate;


// Check if the key already exists
$keyExists = false;
foreach ($secrets['keys'] as $key) {
    if ($key['Key'] === $keyValue) {
        $keyExists = true;
        break;
    }
}


// Add the new key-secret pair to the keys array
if (!$keyExists) {
    $secrets['keys'][] = ['Key' => $keyValue, 'Secret' => $newSecret, 'Added' => $currentUtcTime, 'Account' => $user];
}


// Clean up existing keys
$deletedKeys = [];
foreach ($secrets['keys'] as $index => $key) {
    $keyValue = $key['Key'];
    $keyDate = DateTime::createFromFormat('Y-m-d', $keyValue);

    if ($keyDate === false) {
        // Not a valid date format, skip as it is used for something else
        continue;
    }

    $keyDateString = $keyDate->format('Y-m-d');
    $currentDate = $currentUtcDate;

    if ($keyDateString > $currentDate) {
        // Future dates are invalid - remove
        $deletedKeys[] = $keyValue;
        unset($secrets['keys'][$index]);
    } elseif ($keyDate->format('Y') < 2020) {
        // Before 2020 dates are invalid, remove
        $deletedKeys[] = $keyValue;
        unset($secrets['keys'][$index]);
    }
}
// Encrypt the updated keystore data
$updatedData = json_encode($secrets);
$encryptedData = openssl_encrypt($updatedData, 'AES-256-CBC', $encryptionKey, 0, substr($encryptionKey, 0, 16));


// Write to a timestamped backup file
$timeStamp = gmdate('YmdHis');
$backupFile = "$keystoreFilename.$timeStamp";
file_put_contents($backupFile, $encryptedData);


// Write the encrypted data back to the keystore file __atomically_)
$tmpFile = "$keystoreFilename.tmp";
file_put_contents($tmpFile, $encryptedData);
rename($tmpFile, $keystoreFilename);


// Prepare standard log entry for Keystore log
$logEntry = sprintf("[%s] %s: ", gmdate('Y-m-d H:i:s'), $user);


// If keys were deleted, list them
if (!empty($deletedKeys)) {
    $logEntry .= "Keys deleted: " . implode(', ', $deletedKeys) . ". ";
}


// If keys were added or already existed, make a note of them as well
if ($keyExists) {
    $logEntry .= "Key already exists: $currentUtcDate. No changes made.";
} else {
    $logEntry .= "Key added: $keyValue.";
}


// Write output to the log file
file_put_contents($logFile, $logEntry . "\n", FILE_APPEND);


// Output success message with a newline
print "success".PHP_EOL;


?>

