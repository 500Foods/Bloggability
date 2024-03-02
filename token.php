#!/usr/bin/env php
<?php

// token.php
//
// This is used to help manually manage JWTs and other "secrets" in the Bloggability    
// project, particularly when first getting it setup. Normally, the issuance of JWTs
// is handled by the Login endpoint, but we can use this script to generate JWTs or
// to revoke them, and also to list what JWTs are currently active.


// Most actions are logged using a timer to monitor performance
$startTime = microtime(true);


// Check if running from the command line
if (php_sapi_name() !== 'cli') {
    print "Error: This program can only be executed from the command line.".PHP_EOL;
    exit(99);
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


// Parse command-line arguments
$args = $argv;
array_shift($args); 


// Determine the action description
$actionDescription = count($args) > 0 ? ucfirst($args[0]) : 'View Tokens';
$accountId = $args[1] ?? NULL;
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
    'param3' => $accountId,
    'param4' => 'setpasswd.php',
    'param5' => 'localhost: console',
    'param6' => $actionDescription.' '.$accountId,
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


// Check for command: revoke, decode, or clean
if (!(((count($args) === 1) && ($args[0] === 'list')) || (count($args) === 0))) {

    switch ($args[0]) {

        case 'revoke':

	    // Revoke tokens for a specific account
            if ($accountId) {

                $sql = <<<QUERY
                    DELETE FROM TOKEN  
                        WHERE issued_for = ?
                QUERY;
		$params = [
		    'dtypes' => 's',
		    'param1' => $accountId
		];
                [$results, $affected, $errors] = queryExecute($dbEngine, $db, $sql, $params);

                // Process results
                if (!$results) {
                    print "Error executing statement: ".$errors.PHP_EOL;
                    exit(4);
                 } else {
                     print "TOKEN Rows Affected: ".$affected.PHP_EOL;
		     if ($affected > 0) {
                         print "Revoked token(s) for account_id: $accountId".PHP_EOL;
	             }
                }

            } else {
                print "Please provide an account_id to revoke the token.".PHP_EOL;
            }
            break;

        case 'clean':
		
            // Remove expired tokens for all accounts
            $sql = <<<QUERY
                DELETE FROM TOKEN
                    WHERE expires_at < ?
            QUERY;
            $params = [
                'dtypes' => 's',
                'param1' => gmdate('Y-m-d H:i:s')
            ];
            [$results, $affected, $errors] = queryExecute($dbEngine, $db, $sql, $params);

            // Process results
            if (!$results) {
                print "Error executing statement: ".$errors.PHP_EOL;
                exit(4);
             } else {
                 print "TOKEN Rows Affected: ".$affected.PHP_EOL;
            }
	    break;

        case 'decode':

	    // Decode a specific token
            $tokenIndex = $args[2] ?? 0;

            if ($accountId) {
		    
                // Fetch all tokens for the provided account_id
                $sql = <<<QUERY
                     SELECT
                         token, expires_at, issued_at, issued_by, issued_for
                     FROM
                         TOKEN
                     WHERE
                         issued_for = ?
                     ORDER BY
                         issued_at ASC
                QUERY;
                $params = [
                    'dtypes' => 's',
                    'param1' => $accountId
                ];
                [$results, $affected, $errors] = queryFetch($dbEngine, $db, $sql, $params);

                // Process results
                if ($errors !== 'Success') {
                    print "Error executing statement: ".$errors.PHP_EOL;
                    exit(4);
                 } else {
                     print "TOKEN Rows Retrieved: ".$affected.PHP_EOL;
                }

                $tokenCount = count($results);
                if ($tokenCount > 0) {

                    // Adjust the token index if it's out of bounds
                    if (($tokenIndex < 1) || ($tokenIndex > $tokenCount)) {
                        $tokenIndex = 1;
                    }

                    $token = $results[$tokenIndex - 1]['token'];

                    // Decode and validate the JWT
                    $decodedJWT = decodeJWT($token, $secrets);

                    if ($decodedJWT !== false) {

                        // Assess the JWT
                        print "JWT is valid: ".($decodedJWT['valid'] ? 'Yes' : 'No').PHP_EOL;
                        print "JWT is expired: ".($decodedJWT['expired'] ? 'Yes' : 'No').PHP_EOL;
                        print "Expiration date: ".$decodedJWT['expiration_date'].PHP_EOL;
                        print PHP_EOL."JWT Claims:".PHP_EOL;

                        // Print the claims
                        foreach ($decodedJWT['claims'] as $claim => $value) {
                            if (is_array($value)) {
                                print $claim.":".PHP_EOL;
                                foreach ($value as $item) {
                                    print "- $item".PHP_EOL;
                                }
                            } else {
                                print "$claim: $value".PHP_EOL;
                            }
                        }

			// JWT Signature validation
                        if (!$decodedJWT['valid']) {
                            print PHP_EOL."Signature validation failed. The token may have been tampered with or generated with a different secret.".PHP_EOL;
                        }

                    } else {
                        print "Failed to decode the JWT:".PHP_EOL;
                        print $token.PHP_EOL;
                    }
                } else {
                    echo "No tokens found for account_id: $accountId".PHP_EOL;
                }
            } else {
                print "Please provide an account_id to decode the token.".PHP_EOL;
            }
            break;

        default:
	    
	    // If a valid action isn't selected, then print out the help
            print "Invalid action: ".$args[0].PHP_EOL;
            print " - list".PHP_EOL;
            print " - clean".PHP_EOL;
            print " - revoke <account_id>".PHP_EOL;
            print " - decode <account_id> <optional #>".PHP_EOL;
	    break;
    }

} else {

    // Without any parameters, the default behaviour is to list all the tokens
    $sql = <<<QUERY
        SELECT 
            token, expires_at, issued_at, issued_by, issued_for
        FROM 
            TOKEN
        WHERE
            expires_at > ?
    QUERY;
    $params = [
        'dtypes' => 's',
        'param1' => gmdate('Y-m-d H:i:s')
    ];
    [$results, $affected, $errors] = queryFetch($dbEngine, $db, $sql, $params);

    // Process results
    if ($errors !== "Success") {
        print "Error executing statement: ".$errors.PHP_EOL;
        exit(4);
    } else {
        print "TOKEN Rows Returned: ".$affected.PHP_EOL;
    }

    // Get column names
    $columns = [
        'token', 
	'issued_for', 
	'issued_by', 
	'issued_at', 
	'expires_at'
    ];

    // Print header
    print str_pad($columns[0], 30). 
	  str_pad($columns[1], 20). 
	  str_pad($columns[2], 20). 
	  str_pad($columns[3], 21). 
	  str_pad($columns[4], 20).PHP_EOL;
    print str_repeat('-', 111) . PHP_EOL;

    // Fetch and print the data
    $rowCount = 0;
    while ($rowCount < count($results)) {
	$row = $results[$rowCount];
	print str_pad(substr($row['token'     ],0,25).'...', 30). 
              str_pad(substr($row['issued_for'],0,19), 20). 
	      str_pad(substr($row['issued_by' ],0,19), 20). 
	      str_pad(substr($row['issued_at' ],0,19), 21). 
	      str_pad(substr($row['expires_at'],0,19), 20).PHP_EOL;
        $rowCount++;
    }

    // If no rows were found, print a message
    if ($rowCount === 0) {
        print "No TOKEN records found." . PHP_EOL;
    }
}


// Close database connection
closeConnection($dbEngine, $db);


?>
