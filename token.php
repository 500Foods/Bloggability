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
$actionDescription = count($args) > 0 ? ucfirst($args[0]) : 'View Tokens';
$accountId = $args[1] ?? NULL;

// Log the action
$sql = "INSERT INTO ACTION (action_priority, action_source, action_app_id, action_account_id, action_ip_address, action_description, action_execution_time) VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = $db->prepare($sql);
$stmt->bindValue(1, 1, SQLITE3_INTEGER);
$stmt->bindValue(2, 'admin', SQLITE3_TEXT);
$stmt->bindValue(3, 'token.php', SQLITE3_TEXT);
$stmt->bindValue(4, $accountId, SQLITE3_TEXT);
$stmt->bindValue(5, 'localhost: console', SQLITE3_TEXT);
$stmt->bindValue(6, $actionDescription . ' ' . $accountId, SQLITE3_TEXT);
$stmt->bindValue(7, 0, SQLITE3_INTEGER);
$result = $stmt->execute();

// Check if revoke or decode parameter is provided
if (count($args) > 0) {
    switch ($args[0]) {
        case 'revoke':
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
            break;
        case 'decode':
            $tokenIndex = $args[2] ?? 0;

            if ($accountId) {
                // Fetch all tokens for the provided account_id
                $sql = "SELECT token, expires_at, issued_at, issued_by, issued_for FROM TOKEN WHERE issued_for = ? ORDER BY issued_at ASC";
                $stmt = $db->prepare($sql);
                $stmt->bindValue(1, $accountId, SQLITE3_TEXT);
                $result = $stmt->execute();

                $tokens = [];
                while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                    $tokens[] = $row;
                }

                $tokenCount = count($tokens);
                if ($tokenCount > 0) {
                    // Adjust the token index if it's out of bounds
                    if ($tokenIndex < 0 || $tokenIndex >= $tokenCount) {
                        $tokenIndex = 0;
                    }

                    $token = $tokens[$tokenIndex]['token'];

                    // Decode and validate the JWT
                    $jwtSecret = $constants['Bloggable JWT Secret'];
                    $decodedJWT = decodeJWT($token, $jwtSecret);

                    if ($decodedJWT !== false) {
                      // Assess the JWT
                      echo "\nJWT is valid: " . ($decodedJWT['valid'] ? 'Yes' : 'No') . "\n";
                      echo "JWT is expired: " . ($decodedJWT['expired'] ? 'Yes' : 'No') . "\n";
                      echo "Expiration date: " . $decodedJWT['expiration_date'] . "\n\n";
                      echo "JWT Claims:\n";

                     // Print the claims
                     foreach ($decodedJWT['claims'] as $claim => $value) {
                       if (is_array($value)) {
                        echo "$claim:\n";
                          foreach ($value as $item) {
                            echo "- $item\n";
                         }
                       } else {
                         echo "$claim: $value\n";
                       }
                     }
                      if (!$decodedJWT['valid']) {
                        echo "\nSignature validation failed. The token may have been tampered with or generated with a different secret.\n";
                      }
                    } else {
                        echo  "Failed to decode the JWT:\n";
                        echo $token . PHP_EOL;
                    }
                } else {
                    echo "No tokens found for account_id: $accountId\n";
                }
            } else {
                echo "Please provide an account_id to decode the token.\n";
            }
            break;
        default:
            echo "Invalid action: " . $args[0] . "\n";
            echo " - No parameters: List Tokens\n";
            echo " - revoke <account_id>\n";
            echo " - decode <account_id> <optional #>\n";

            break;
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
        $columns = ['token', 'issued_for', 'issued_by', 'issued_at', 'expires_at'];

        // Print header
        echo str_pad($columns[0], 30) . str_pad($columns[1], 20) . str_pad($columns[2], 20) . str_pad($columns[3], 21) . str_pad($columns[4], 20) . PHP_EOL;

        // Print divider
        echo str_repeat('-', 111) . PHP_EOL;

        // Fetch and print the data
        $rowCount = 0;
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            echo str_pad(substr($row['token'],0,25) . '...', 30) . str_pad($row['issued_for'], 20) . str_pad($row['issued_by'], 20) . str_pad($row['issued_at'], 20) . ' ' . str_pad($row['expires_at'], 20) . PHP_EOL;
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

/**
 * Decodes and validates a JWT token.
 *
 * @param string $token     The JWT token to decode.
 * @param string $jwtSecret The JWT secret used for signing.
 *
 * @return array|false      An array containing the claims, validity status, and expiration status, or false on failure.
 */
function decodeJWT($token, $jwtSecret)
{
    // Split the JWT into its three parts
    $parts = explode('.', $token);
    if (count($parts) !== 3) {
        echo "DecodeJWT: Not enough parts\n";
        return false;
    }

    // Decode the payload part
    $payload = json_decode(base64_decode($parts[1]), true);
    if ($payload === null) {
        echo "DecodeJWT: Payload could not be decoded\n";
        return false;
    }

    // Extract the account_id from the payload
    $accountId = $payload['acc_id'] ?? null;
    if ($accountId === null) {
        echo "DecodeJWT: Account not found in claims\n";
        return false;
    }

    // Compute the expected hash
    $header = base64_decode($parts[0]);
    if ($header === false) {
        echo "DecodeJWT: Header could not be decoded\n";
        return false;
    }

    // What should it be? This is a URL-encoded base64 string
    $expectedHash = str_replace(['+','/','='], ['-','_',''], base64_encode(hash_hmac('sha256', $parts[0] . '.' . $parts[1], $jwtSecret . $accountId, true)));

    // The actual hash is the third part of the JWT token
    $actualHash = $parts[2];

    // Check if the hashes match
    $valid = hash_equals($expectedHash, $actualHash);
    if (!$valid) {
      echo "Expected: $expectedHash\n";
      echo "Actual: $actualHash\n";
    }

    // Check if the token has expired
    $expired = isset($payload['exp']) && $payload['exp'] < time();
    //echo $payload['exp'] . ': ' . date('Y-m-d H:i:s', $payload['exp']) . PHP_EOL;
    //echo time() . ': ' . date('Y-m-d H:i:s', time()) . PHP_EOL;
    return [
        'claims' => $payload,
        'valid' => $valid,
        'expired' => $expired,
        'expiration_date' => date('Y-m-d H:i:s', $payload['exp'])
    ];
}

?>
