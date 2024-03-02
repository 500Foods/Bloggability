<?php

// dbsupport.php
// 
// This contains a number of functions used to help make writing Bloggability code
// a little less cumbersome, particualrly when it comes to using multiple databases
// or when using boilerplate code for new REST API endpoints.
// 
// function getSecrets() - used to find the keystore, decrypt it and make the keys avaialble
// function getConnection() - returns everything we need to work with the current database
// function executeQuery() - runs a query against the database, returning data and status information
// function closeConnection() - closes the database
// function base64_url_encode() - replaces special characters with other special characters
// function encodeJWT() - takes in claims and generates the JWT
// function decodeJWT() - returns the claims, expiration, and signuature status of a JWT
// function generateRandomSecret() - generates a random string with alpha, numeric, and symbol chars
// function getLookups() - returns one of the lookup sets from the LOOKUP table
//
// Generally speaking these should all return either false, meaning that there's nothing available,
// or some other value that the caller can use to decide whether it is fatal enough to justify exit().



function getSecrets($constants) 
{
    // Get the keystore filename and encryption key from the configuration JSON
    $keystoreFilename = $constants['Bloggability Keystore'] ?? null;
    $encryptionKey = $constants['Bloggability Keystore Key'] ?? null;

    if (!$keystoreFilename || !$encryptionKey) {
        print "Keystore or Keystore Key were not found in the configuration file.".PHP_EOL;
        return false;
    }

    // Read the existing keystore data and decrypt it
    $keystoreData = file_get_contents($keystoreFilename);
    $decryptedData = openssl_decrypt($keystoreData, 'AES-256-CBC', $encryptionKey, 0, substr($encryptionKey, 0, 16));
    $secrets = json_decode($decryptedData, true);
  
    return $secrets;
}



function getConnection($constants)
{
    // Get database engine and database name from configuration
    $dbEngine = $constants['Bloggability Database Engine'];
    $dbName = $constants['Bloggability Database'];

    if (!$dbEngine || !$dbName) {
        print "Database or Database Engine were not found in the configuration file.".PHP_EOL;
        return false;
    }

    if ($dbEngine === 'SQLite') {

        // Check if SQLite3 extension is loaded
        if (!extension_loaded('sqlite3')) {
            print "Error: SQLite3 extension is not loaded.".PHP_EOL;
            return false;
        }

        // Try to connect to SQLite database
        try {
            $db = new SQLite3($dbName);
        } catch (Exception $e) {
            print "Error: Unable to connect to SQLite database.".PHP_EOL;
            print $e->getMessage().PHP_EOL;
            return false;
        }

    } elseif ($dbEngine === 'DB2') {
	   
        // Check if ibm_db2 extension is loaded
        if (!extension_loaded('ibm_db2')) {
            print "Error: ibm_db2 extension is not loaded.".PHP_EOL;
            return false;
        }

        // Get additional database connection parameters from the configuration
        $dbUser = $constants['Bloggability Database User'] ?? null;
        $dbPass = $constants['Bloggability Database Pass'] ?? null;

        // Check if all required parameters are provided
        if (!$dbUser || !$dbPass)  {
	    print "Database User or Database Pass were not found in the configuration file.".PHP_EOL;
            return false;
        }

	// Set this (obscure?) DB2 option to have columns returned as lowercase to match other DBs
	$options =  array('DB2_ATTR_CASE' => DB2_CASE_LOWER);

        // Try to connect to the DB2 database
        try {
            $db = db2_connect($dbName, $dbUser, $dbPass, $options);
        } catch (Exception $e) {
	    print "Error: Unable to connect to DB2 database.".PHP_EOL;
	    print $e->getMessage().PHP_EOL;
            return false;
        }

    } elseif ($dbEngine === 'MySQL') {
	    
        // Check if mysqli extension is loaded
        if (!extension_loaded('mysqli')) {
            print "Error: mysqli extension is not loaded.".PHP_EOL;
            return false;
        }

        // Get addtional database connection parameters from the configuration
        $dbUser = $constants['Bloggability Database User'] ?? null;
        $dbPass = $constants['Bloggability Database Pass'] ?? null;
        $dbHost = $constants['Bloggability Database Host'] ?? null;
        $dbPort = $constants['Bloggability Database Port'] ?? null;

        // Check if all required parameters are provided
        if (!$dbUser || !$dbPass || !$dbHost || !$dbPort)  {
            print "Database User, Pass, Host, or Port were not found in the configuration file.".PHP_EOL;
            return false;
        }

        // Try to connect to the MySQL database
        try {
            $db = new mysqli($dbHost, $dbUser, $dbPass, $dbName, $dbPort);
        } catch (Exception $e) {
            print "Error: Unable to connect to MySQL database.".PHP_EOL;
            print $e->getMessage().PHP_EOL;
            return false;
        }

    } else {
        print "Error: Unsupported database engine '$dbEngine'.".PHP_EOL;
        return false;
    }

    // Return the db connection, name, and engine so they can be used
    // for subsequent calls to queryExecute() and its relatives.
    return [$db, $dbName, $dbEngine];

}



function queryExecute($dbEngine, $db, $sql, $params) {

    // Decode $params: [$dtypes, $param1, $param2, $param3, ...]
    // $dtypes is a character string indicating data types (i = integer, s = string, etc.)
    // Eg: $dtypes = "ssi" means three parameters, two strings followed by an integer
    extract($params);

    if ($dbEngine === 'SQLite') {

	// Most queries will have parameters
        $stmt = $db->prepare($sql);
        if (!$stmt) {
            return [false, -1, "Error preparing statement: ".$db->error.PHP_EOL];
        }

	// Bind parameters using $dtypes to figure out what kind they are
        for ($i = 1; $i < count($params); $i++) {
	    if (substr($dtypes, $i - 1, 1) === 'i') {
                $stmt->bindValue($i, $params['param'.$i], SQLITE3_INTEGER);
	    } elseif (substr($dtypes, $i-1,1) === 's') {
                $stmt->bindValue($i, $params['param'.$i], SQLITE3_TEXT);
	    }
        }

	// Execute the query
        $result = $stmt->execute(); 

	// Deal with the results
        if (!$result) {
            return [$result, -1, "Error executing statement: ".$stmt->error.PHP_EOL];
        } else {
	    return [$result, $db->changes(), "Success"];
        }
  

    } elseif ($dbEngine === 'DB2') {

	// Most queries will have parameters
        $stmt = db2_prepare($db, $sql);
        if (!$stmt) {
            return [false, -1, "Error preparing statement: ".$db->error.PHP_EOL];
        }
       
        // Bind parameters - assuming they are all "in" parameters
        for ($i = 1; $i < count($params); $i++) {
            db2_bind_param($stmt, $i, 'param'.$i, DB2_PARAM_IN);
        }
    
	// Execute the query
        $result = db2_execute($stmt);

	// Deal with the results
        if (!$result) {
            return [$result, -1, "Error executing statement: ".$stmt->error.PHP_EOL];
        } else {
            return [$result, db2_num_rows($stmt), "Success"];
        }


    } elseif ($dbEngine === 'MySQL') {

	// Most queries will have parameters
        $stmt = $db->prepare($sql);
        if (!$stmt) {
            return [false, -1, "Error preparing statement: ".$db->error.PHP_EOL];
        }
  
	// Bind parameters using $dtypes to indicate what kind they are
        $paramlist = array();
        for ($i = 1; $i < count($params); $i++) {
            $paramlist[] = $params['param'.$i];
        }
        $stmt->bind_param($dtypes, ...$paramlist);
  
	// Execute the query
        $result = $stmt->execute();

	// Deal with the results
        if (!$result) {
            return [$result, -1, "Error executing statement: ".$stmt->error.PHP_EOL];
        } else {
            return [$result, $db->affected_rows, "Success"];
        }
    }
}


function queryFetch($dbEngine, $db, $sql, $params) {

    // Decode $params: [$dtypes, $param1, $param2, $param3, ...]
    // $dtypes is a character string indicating data types (i = integer, s = string, etc.)
    // Eg: $dtypes = "ssi" means three parameters, two strings followed by an integer
    extract($params);

    if ($dbEngine === 'SQLite') {

        // Most queries will have parameters
        $stmt = $db->prepare($sql);
        if (!$stmt) {
            return [false, -1, "Error preparing statement: ".$db->error.PHP_EOL];
        }

        // Bind parameters using $dtypes to figure out what kind they are
        for ($i = 1; $i < count($params); $i++) {
            if (substr($dtypes, $i - 1, 1) === 'i') {
                $stmt->bindValue($i, $params['param'.$i], SQLITE3_INTEGER);
            } elseif (substr($dtypes, $i-1,1) === 's') {
                $stmt->bindValue($i, $params['param'.$i], SQLITE3_TEXT);
            }
        }

        // Execute the query
        $result = $stmt->execute();

        // Deal with the results
        if (!$result) {
            return [$result, -1, "Error executing statement: ".$stmt->error.PHP_EOL];
        } else {
            $resultset = array();
            while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                $resultset[] = $row;
            }
            if (count($resultset) === 0) {
                return [$resultset, 0, "Success"];
            } else {
                return [$resultset, count($resultset), "Success"];
            }
        }

    } elseif ($dbEngine === 'DB2') {

        // Most queries will have parameters
        $stmt = db2_prepare($db, $sql);
        if (!$stmt) {
            return [false, -1, "Error preparing statement: ".$db->error.PHP_EOL];
        }

        // Bind parameters - assuming they are all "in" parameters
        for ($i = 1; $i < count($params); $i++) {
            db2_bind_param($stmt, $i, 'param'.$i, DB2_PARAM_IN);
        }

        // Execute the query
        $result = db2_execute($stmt);

        // Deal with the results
        if (!$result) {
            return [$result, -1, "Error executing statement: ".$stmt->error.PHP_EOL];
        } else {
            $resultset = array();
            if (db2_num_rows($stmt) === 0) {
                return [$resultset, 0, "Success"];
            } else {
                while ($row = db2_fetch_assoc($stmt)) {
                    $resultset[] = $row;
                }
                return [$resultset, count($resultset), "Success"];
            }
        }

    } elseif ($dbEngine === 'MySQL') {

        // Most queries will have parameters
        $stmt = $db->prepare($sql);
        if (!$stmt) {
            return [false, -1, "Error preparing statement: ".$db->error.PHP_EOL];
        }

        // Bind parameters using $dtypes to indicate what kind they are
        $paramlist = array();
        for ($i = 1; $i < count($params); $i++) {
            $paramlist[] = $params['param'.$i];
        }
        $stmt->bind_param($dtypes, ...$paramlist);

        // Execute the query
	$stmt->execute();
        $result = $stmt->get_result();

        // Deal with the results
        if (!$result) {
            return [$result, -1, "Error executing statement: ".$stmt->error.PHP_EOL];
        } else {
            $resultset = array();
            if ($db->affected_rows === 0) {
                return [$resultset, 0, "Success"];
            } else {
                while ($row = $result->fetch_assoc()) {
                    $resultset[] = $row;
                }
                return [$resultset, count($resultset), "Success"];
	    }
        }
    }
}


function closeConnection($dbEngine, $db) 
{
    if ($dbEngine === 'SQLite') {
        $db->close();
    } elseif ($dbEngine === 'DB2') {
        db2_close($db);
    } elseif ($dbEngine === 'MySQL') {
       $db->close();
    }
}



function base64_url_encode($text):String
{
    return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($text));
}



function createJWT($accountId, $appId, $weblogId, $accountStatus, $accountName, $accountEmail, $weblogs, $authors, $key, $secret) 
{
    $header = [
        "alg" => "HS256",
        "typ" => "JWT"
    ];

    $claims = [
        'iat' => time(),
        'exp' => time() + 3600,
        'key' => $key,
        'iss' => $appId,
	'aud' => $_SERVER['SERVER_NAME'] ?? 'localhost: console',
        'acc_id' => $accountId,
        'web_id' => $weblogId,
        'acc_status' => $accountStatus,
        'acc_name' => $accountName,
        'acc_email' => $accountEmail,
        'weblogs' => $weblogs,
        'authors' => $authors
    ];

    $header = base64_url_encode(json_encode($header));
    $claims = base64_url_encode(json_encode($claims));
    $signature = base64_url_encode(hash_hmac('sha256', "$header.$claims", $secret, true));
    $jwt = "$header.$claims.$signature";
    return $jwt;
}


function decodeJWT($token, $keys)
{
    // Split the JWT into its three parts
    $parts = explode('.', $token);
    if (count($parts) !== 3) {
        print "DecodeJWT: Not enough parts.".PHP_EOL;
        return false;
    }

    // Decode the payload part
    $payload = json_decode(base64_decode($parts[1]), true);
    if ($payload === null) {
        print "DecodeJWT: Payload could not be decoded.".PHP_EOL;
        return false;
    }

    // Extract the key value from the payload
    $keyValue = $payload['key'] ?? null;
    if ($keyValue === null) {
        print "DecodeJWT: Key not found in claims.".PHP_EOL;
        return false;
    }

    // Search for the corresponding secret key from the keystore
    $secret = null;
    foreach ($keys['keys'] as $keyEntry) {
        if ($keyEntry['Key'] === $keyValue) {
            $secret = $keyEntry['Secret'];
            break;
        }
    }

    // If the secret key is not found, return false
    if ($secret === null) {
        print "DecodeJWT: Secret key not found in the keystore.".PHP_EOL;
        return false;
    }

    // Compute the expected hash
    $header = base64_decode($parts[0]);
    if ($header === false) {
        print "DecodeJWT: Header could not be decoded.".PHP_EOL;
        return false;
    }

    // What should it be? This is a URL-encoded base64 string
    $expectedHash = str_replace(['+','/','='], ['-','_',''], base64_encode(hash_hmac('sha256', $parts[0] . '.' . $parts[1], $secret, true)));

    // The actual hash is the third part of the JWT token
    $actualHash = $parts[2];

    // Check if the hashes match
    $valid = hash_equals($expectedHash, $actualHash);
    if (!$valid) {
      print "DecodeJWT: Signature mismatch:".PHP_EOL;
      print "Expected: $expectedHash".PHP_EOL;
      print "Actual: $actualHash".PHP_EOL;
    }

    // Check if the token has expired
    $expired = isset($payload['exp']) && $payload['exp'] < time();
    if ($expired) {
       print "DecodeJWT: Token has expired:".PHP_EOL;
       print "JWT=".$payload['exp'].': '.date('Y-m-d H:i:s', $payload['exp']).PHP_EOL;
       print "NOW=".time().': '.date('Y-m-d H:i:s', time()).PHP_EOL;
    }

    // Return everything we know about the JWT
    return [
        'claims' => $payload,
        'valid' => $valid,
        'expired' => $expired,
        'expiration_date' => date('Y-m-d H:i:s', $payload['exp'])
    ];
}



function generateRandomSecret($length, $alphaCount, $numCount, $symbolCount) {

    # Generate secret that guarantees the inclusion of a selected number of 
    # alpha, numeric, and symbol characters for suitable complexity.
    
    $alpha = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $num = '0123456789';
    $symbol = '!@#$%^&*-_+=?.,{}[]';

    $secret = '';
    $secret .= substr(str_shuffle($alpha.$alpha), 0, $alphaCount);
    $secret .= substr(str_shuffle($num.$num), 0, $numCount);
    $secret .= substr(str_shuffle($symbol.$symbol), 0, $symbolCount);

    return str_shuffle($secret);
}



function getLookups($dbEngine, $db, $lookup_id) {
	
    // Get lookup by id
    $sql = <<<QUERY
        SELECT
            lookup_key, lookup_value
        FROM
            LOOKUP
        WHERE
            lookup_id = ?
    QUERY;
    $params = [
        'dtypes' => 'i',
	'param1' => $lookup_id
    ];
    [$lookups, $affected, $errors] = queryFetch($dbEngine, $db, $sql, $params);

    if ($errors !== 'Success') {
        print "Error executing statement: ".$errors.PHP_EOL;
        exit(4);
    }
   
    // Rearrrange resultset
    $lookup_entries = array();
    foreach ($lookups as $lookup) {
        $lookup_entries[$lookup['lookup_key']] = $lookup['lookup_value'];
    }

    return $lookup_entries;
}


?>
