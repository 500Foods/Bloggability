#!/usr/bin/env php
<?php
if (ob_get_level()) {
    $buf = ob_get_clean();
    ob_start();
    echo substr($buf, 0, strpos($buf, file(__FILE__)[0]));
}

// bloggability.php
//
// This is the REST API implementation for the back-end of the Bloggability project
//
// BlogAPI:
// - handleRequest()
// - Authorization Services:
//   - Login
//   - Renew
//   - Logout
//   - LogoutAll
//   - LogAction
// - Blog Services
//   - Welcome
//   - RSS


// Most actions are logged using a timer to monitor performance
$startTime = microtime(true);


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



// Check that we have some basic values
$mainTitle = $constants['Bloggability Title'] ?? null;
$mainDescription = $constants['Bloggability Description'] ?? null;
$mainURL = $constants['Bloggability URL'] ?? null;
if ($mainTitle === null || $mainDescription === null || $mainURL === null) {
    print "Error: Basic JSON parameters not supplied (Title, Description, URL).".PHP_EOL;
    exit(98);
}


// Get secret keys from special keystore location
$secrets = getSecrets($constants);
if (!$secrets) {
    print "Error: Keystore not available.".PHP_EOL;
    exit(5);
}


// Get the "ACCOUNT" key and today's date key from the keystore 
$keystoreAccountKey = null;
$keystoreDateKey = null;
$currentUtcDate = gmdate('Y-m-d');
$currentUtcTime = gmdate('Y-m-d H:i:s');

foreach ($secrets['keys'] as $keyEntry) {
    if ($keyEntry['Key'] === 'ACCOUNT') {
        $keystoreAccountKey = $keyEntry['Secret'];
    } elseif ($keyEntry['Key'] === $currentUtcDate) {
        $keystoreDateKey = $keyEntry['Secret'];
    }
}


// If either the "ACCOUNT" or today's date key is missing, exit with an error
if ($keystoreAccountKey === null || $keystoreDateKey === null) {
    print "Error: ACCOUNT key and/or today's date key not found in the keystore.".PHP_EOL;
    exit(1); 
}


// Establish connection to the database
[$db, $dbName, $dbEngine] = getConnection($constants);
if (!$db || !$dbName || !$dbEngine) {
    print "Error: Database connection not established.".PHP_EOL;
    exit(2);
}
 

/**
 * @OA\Info(  
 *   version="1.0.3",    
 *   title="Bloggablity",   
 *   description="REST API for Blogabbility service",  
 * )   
 **/  


/**   
 *  
 * @OA\SecurityScheme(    
 *   type="http",     
 *   scheme="bearer",    
 *   bearerFormat="JWT",     
 *   securityScheme="jwtAuth",   
 * )    
 **/


class BlogAPI {

    // Routes incoming REST API requests to the appropriate endpoint
    public function handleRequest($uri, $method, $params) {

	$startTime = $GLOBALS['startTime'];

        if(method_exists($this,$uri)) {  
            $this->$uri($params);
        } else {
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'localhost: console';
            $this->returnError("Endpoint", "Missing Endpoint: " . $uri, $ipAddress, "N/A", "N/A", "N/A", $startTime);
        }
    }

    
    // Logs all actions to the ACTIONS table
    private function logAction($appId, $ipAddress, $functionName, $weblogId, $message, $startTime) {

	$dbEngine = $GLOBALS['dbEngine'];
	$db = $GLOBALS['db'];
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
            'param2' => $functionName,
            'param3' => $weblogId,
            'param4' => $appId,
            'param5' => $ipAddress,
	    'param6' => $message,
            'param7' => $runningTime
        ];
        [$results, $affected, $errors] = queryExecute($dbEngine, $db, $sql, $params);

        // Process the results
        if (!$results) {
            print "Error executing statement: ".$errors.PHP_EOL;
            exit(4);
        }
    }


    // Returns an error if something was encountered while executing an endpoint
    private function returnError($endpoint, $error, $ipAddress, $appId, $accountId, $weblogId, $startTime) {

        // Log the error
        $message = "$endpoint failed due to $error";
        $this->logAction($appId, $ipAddress, $endpoint, $weblogId, $message, $startTime);

        // Return error response
        header('Content-Type: application/json');
        print json_encode(['Status' => "Error $error"]) . PHP_EOL;
	exit;
    }


    /**
     * @OA\Get(  
     *   path="/api/login",    
     *   tags={"Authorization Services"},       
     *   summary="Login",    
     *   @OA\Parameter(name="apikey",    in="query", required=true, @OA\Schema(type="string")), 
     *   @OA\Parameter(name="email",     in="query", required=true, @OA\Schema(type="string")),
     *   @OA\Parameter(name="password",  in="query", required=true, @OA\Schema(type="string")),
     *   @OA\Parameter(name="weblog", in="query", required=true, @OA\Schema(type="string")),
     *   @OA\Response(response=200, description="Successful login"),
     *   @OA\Response(response=400, description="Validation error")
     * )  
     **/

    public function Login($params) {
	    
        $db = $GLOBALS['db'];
	$dbEngine = $GLOBALS['dbEngine'];
        $startTime = $GLOBALS['startTime'];

        // Default values (for logging purposes initially)
        $accountId = 'N/A';
        $appId = 'N/A';

        // Set IP address
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'localhost: console';

        // Extract parameters
        $apikey = $params['apikey'] ?? null;
        $email = $params['email'] ?? null;
        $password = $params['password'] ?? null;
        $weblogId = $params['weblog'] ?? null;

        // Check that we've got all the parameters that we need to proceed
        if (!$apikey || !$email || !$password || !$weblogId) {
            $this->returnError('Login', 'Login failed: Incomplete credentials', $ipAddress, $appId, $accountId, $weblogId, $startTime);
        }

        // Validate APIKEY
        $sql = <<<QUERY
            SELECT             
                apikey, app_id
            FROM
                APIKEY
            WHERE
                (apikey = ?)
                AND (issued_at < ?)
                AND (expires_at > ?)
            QUERY;
        $params = [
            'dtypes' => 'sss',
            'param1' => $apikey,
            'param2' => gmdate('Y-m-d H:i:s'),
            'param3' => gmdate('Y-m-d H:i:s')
        ];
        [$results, $affected, $errors] = queryFetch($dbEngine, $db, $sql, $params);
        
        // Process the results
        if ($errors !== 'Success') {
            print "Error executing statement: ".$errors.PHP_EOL;
            exit(4);
	}

        // Check if APIKEY is valid - expect exactly one row to be returned
        if ($affected !== 1) {
            $this->returnError("Login", "Login failed: Invalid APIKEY", $ipAddress, $appId, $accountId, $weblogId, $startTime);
        }

        // Get app_id
        $appId = $results[0]['app_id'];

        // Check Account
        $sql = <<<QUERY
            SELECT
                account_id, account_name, account_email, account_password_hash, account_status
            FROM
                ACCOUNT
            WHERE
                account_email = ?
            QUERY;
        $params = [
            'dtypes' => 's',
            'param1' => $email
        ];
        [$results, $affected, $errors] = queryFetch($dbEngine, $db, $sql, $params);

        // Process the results
        if ($errors !== 'Success') {
            print "Error executing statement: ".$errors.PHP_EOL;
            exit(4);
        }

        // Check if APIKEY is valid - expect exactly one row to be returned
        if ($affected !== 1) {
            $this->returnError("Login", "Login failed: E-Mail address not found.", $ipAddress, $appId, $accountId, $weblogId, $startTime);
	}

        // Get account info
        $passwordHash = $results[0]['account_password_hash'];
        $accountId = $results[0]['account_id'];
        $accountStatus = $results[0]['account_status'];
        $accountName = $results[0]['account_name'];
        $accountEmail = $results[0]['account_email'];
    
        // Account disabled
        if ($accountStatus === 0) {
            $this->returnError("Login", "Login failed: Account disabled.", $ipAddress, $appId, $accountId, $weblogId, $startTime);
        }

        // Verify password
        $hash = hash('sha256', $GLOBALS['keystoreAccountKey'].trim($accountId).$password);
        if ($hash != $passwordHash) {
            $this->returnError("Login", "Login failed: Invalid password.", $ipAddress, $appId, $accountId, $weblogId, $startTime);
        }

        // Get weblog access info
        $sql = <<<QUERY
            SELECT
                weblog_id, weblog_account_role
            FROM
                WEBLOG_ACCOUNT
            WHERE
                account_id = ?
            QUERY;
        $params = [
            'dtypes' => 's',
            'param1' => $accountId
        ];
	[$results, $affected, $errors] = queryFetch($dbEngine, $db, $sql, $params);

        // Process the results
        if ($errors !== 'Success') {
            print "Error executing statement: ".$errors.PHP_EOL;
            exit(4);
        }

        $weblogs = [];
	$rowCount = 0;
        while ($rowCount < count($results)) {
	    $row = $results[$rowCount];
            $weblogs[$row['weblog_id']] = $row['weblog_account_role'];
	    $rowCount++;
        }

        // Get author access info
        $sql = <<<QUERY
            SELECT
                author_id, author_account_status
            FROM
                AUTHOR_ACCOUNT
            WHERE
                account_id = ?
            QUERY;
        $params = [
            'dtypes' => 's',
            'param1' => $accountId
        ];
        [$results, $affected, $errors] = queryFetch($dbEngine, $db, $sql, $params);

        // Process the results
        if ($errors !== 'Success') {
            print "Error executing statement: ".$errors.PHP_EOL;
            exit(4);
        }

        $authors = [];
        $rowCount = 0;
        while ($rowCount < count($results)) {
            $row = $results[$rowCount];
            $authors[$row['author_id']] = $row['author_account_status'];
            $rowCount++;
        }

        // Create JWT with claims including weblogs and authors
        $jwt = createJWT($accountId, $appId, $weblogId, $accountStatus, $accountName, $accountEmail, $weblogs, $authors, $GLOBALS['currentUtcDate'], $GLOBALS['keystoreDateKey']);
    
        // Store the JWT in the database
        $sql = <<<QUERY
            INSERT INTO TOKEN
                (token, issued_by, issued_at, expires_at, issued_for)
            VALUES
                (?, ?, ?, ?, ?)
            QUERY;
        $params = [
            'dtypes' => 'sssss',
            'param1' => $jwt,
            'param2' => $appId,    
            'param3' => gmdate('Y-m-d H:i:s'), 
            'param4' => gmdate('Y-m-d H:i:s', time()+3600),
            'param5' => $accountId
        ];
        [$results, $affected, $errors] = queryExecute($dbEngine, $db, $sql, $params);

        // Process the results
        if ($errors !== 'Success') {
            print "Error executing statement: ".$errors.PHP_EOL;
            exit(4);
	}

        // Log successful login
        $message = "Successful login for $email";
        $this->logAction($appId, $ipAddress, 'Login', $weblogId, $message, $startTime);

        // Return success response
        header('Content-Type: application/json');
        $response = [
            'Status' => 'Success',
            'jwt' => $jwt
        ];
        print json_encode($response).PHP_EOL;

        // Close database connection
        closeConnection($dbEngine, $db);
    }

    /**
     *  
     * @OA\Get(  
     *   path="/api/welcome",    
     *   tags={"Blog Services"},       
     *   summary="Get blog welcome data",    
     *   @OA\Parameter(
     *       name="bloggable_id", 
     *       in="query", 
     *       required=false,  
     *       description="Bloggable ID",
     *       @OA\Schema(  
     *           type="string",    
     *           default="bloggable"       
     *       ) 
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Successful response",
     *   )
     * )
     **/

    public function Welcome($params) {

        // Start timer
        $startTime = microtime(true);

        // Check if SQLite3 extension is loaded
        if (!extension_loaded('sqlite3')) {
            print "SQLite3 extension is not loaded. Please enable it in your PHP configuration." . PHP_EOL;
            return;
        }

        // Extract the parameters we're interested in from $params
        $appId = $params['app_id'] ?? 'console';
        $weblogId = $params['bloggable_id'] ?? 'bloggable';
    
        // Set the IP address based on the environment
        $ipAddress = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'localhost';

        // Log the action
        $this->logAction($appId, $ipAddress, "Welcome", $weblogId, $startTime);

        // Connect to SQLite database
        $db = new SQLite3($GLOBALS['constants']['Bloggable Database']);

        // Prepare and execute SQL statement
        $sql = "SELECT * FROM WEBLOG WHERE weblog_id = ?";
        $stmt = $db->prepare($sql);
        $stmt->bindValue(1, $weblogId, SQLITE3_TEXT);
        $result = $stmt->execute();

        // Fetch the data
        $weblogData = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $weblogData[] = $row;
        }

        // Close database connection
        $db->close();
    
        // Return the data as JSON
        header('Content-Type: application/json');
        print json_encode($weblogData);
    }


    public function RSS($params) {

        $db = $GLOBALS['db'];
        $dbEngine = $GLOBALS['dbEngine'];
        $startTime = $GLOBALS['startTime'];
	$mainURL = $GLOBALS['mainURL'];

	// Default values (for logging purposes initially)
        $accountId = 'N/A';
	$appId = 'N/A';

        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'localhost: console';

        // Extract parameters
        $weblogId = $params['weblog'] ?? null;

        // Check that we've got all the parameters that we need to proceed
        if (!$weblogId) {
            $this->returnError('RSS', 'RSS request failed: Incomplete credentials', $ipAddress, $appId, $accountId, $weblogId, $startTime);
	}

        // Log the request
        $message = "RSS feed requested for weblog: $weblogId";
        $this->logAction('RSS', $ipAddress, 'RSS', $weblogId, $message, $startTime);

        // Get the weblog's last update timestamp
        $sql = <<<QUERY
            SELECT
                weblog_name, weblog_description, weblog_url, weblog_thumbnail_url, weblog_image_url,
                weblog_image_alt, weblog_favicon_url, weblog_language, weblog_copyright, weblog_latest_at
            FROM
                WEBLOG
            WHERE
                (weblog_id = ?)
                AND (weblog_status = 1)
            QUERY;
        $params = [
            'dtypes' => 's',
            'param1' => $weblogId
        ];
        [$results, $affected, $errors] = queryFetch($dbEngine, $db, $sql, $params);

        if ($errors !== 'Success') {
            print "Error executing statement: ".$errors.PHP_EOL;
            exit(4);
        }

        if ($affected === 1) {
            $weblogLatestAt = $results[0]['weblog_latest_at'];
            $weblog = $results[0];
	} else {
	    $this->returnError('RSS', 'RSS request failed: Feed not found', $ipAddress, $appId, $accountId, $webLogId, $startTime);
	}

	// Check for current XML file
        $rssFilePath = "rss/$weblogId.xml";
        if (file_exists($rssFilePath)) {
            $fileModifiedTime = filemtime($rssFilePath);

	    // RSS file is current, so let's use it
            if ($fileModifiedTime >= strtotime($weblogLatestAt)) {

                header('Content-Type: application/rss+xml; charset=utf-8');
                readfile($rssFilePath);
                exit;
            }
        }

	// Get blog posts (last year or maximum 100 posts)
        $sql = <<<QUERY
            SELECT
                blog_id, blog_author_id, blog_title, blog_url, blog_thumbnail_url, blog_image_url,
                blog_image_alt, blog_summary, blog_content, blog_categories, blog_tags, blog_publish_at,
                AUTHOR.author_name, AUTHOR.author_email
            FROM
                BLOG
                LEFT OUTER JOIN AUTHOR
                on BLOG.blog_author_id = AUTHOR.author_id
            WHERE
                (blog_weblog_id = ?)
                AND (blog_publish_at <= ?)
                AND (blog_status = 3)
            ORDER BY
                blog_publish_at DESC
            LIMIT 100
        QUERY;
        $params = [
            'dtypes' => 'ss',
            'param1' => $weblogId,
            'param2' => gmdate('Y-m-d H:i:s')
        ];
        [$results, $affected, $errors] = queryFetch($dbEngine, $db, $sql, $params);

        if ($errors !== 'Success') {
            print "Error executing statement: ".$errors.PHP_EOL;
            exit(4);
        }
	
        $blog_tags = getLookups($dbEngine,$db, 7);
        $blog_categories = getLookups($dbEngine, $db, 8);

	// Got $wweblog (header) and $blogPosts (details), so let's generate XML
        $blogPosts = $results;

        // Generate the RSS XML
	$rssFeed = new SimpleXMLElement(<<<XML
            <?xml version="1.0" 
                  encoding="UTF-8"
            ?>
            <rss version="2.0" 
                 xmlns:dc="http://purl.org/dc/elements/1.1/"
                 xmlns:content="http://purl.org/rss/1.0/modules/content/"
                 xmlns:admin="http://webns.net/mvcb/"
                 xmlns:atom="http://www.w3.org/2005/Atom"
            ></rss>
            XML);

        $channel = $rssFeed->addChild('channel');
        $channel->addChild('title',       $weblog['weblog_name']);
        $channel->addChild('link',        $mainURL.$weblog['weblog_url']);
        $channel->addChild('description', $weblog['weblog_description']);
        $channel->addChild('language',    $weblog['weblog_language'] ?? 'en');
        $channel->addChild('copyright',   $weblog['weblog_copyright'] ?? 'No copyright information supplied');
	$atom = $channel->addChild('atom:atom:link'); 
        $atom->addAttribute('href', $mainURL.'rss-'.$weblogId.'.xml');
        $atom->addAttribute('rel', 'self');
        $atom->addAttribute('type', 'application/rss+xml');

        // Add blog posts as RSS items
        foreach ($blogPosts as $post) {
            $item = $channel->addChild('item');
            $item->addChild('title',       $post['blog_title']);
            $item->addChild('link',        $mainURL.$post['blog_url']);
            $item->addChild('description', $post['blog_summary']);
            $item->addChild('author',      $post['author_email'].' ('.$post['author_name'].')');
            $item->addChild('pubDate',     date(DATE_RSS, strtotime($post['blog_publish_at'])));
            $item->addChild('guid',        $mainURL.$post['blog_id']);

            // Add categories
            $categories = explode(';', trim($post['blog_categories'],';'));
            foreach ($categories as $category) {
		if ($blog_categories[$category] ?? false) {
                    $item->addChild('category', $blog_categories[$category]);
	        }
            }

	    // Add tags (as categories)
            $tags = explode(';',trim($post['blog_tags'],';'));
            foreach ($tags as $tag) {
		if ($blog_tags[$tag] ?? false) {
                    $item->addChild('category', $blog_tags[$tag]);
		}
            }

            // Add featured image as enclosure
            if (!empty($post['blog_image_url'])) {
                $enclosure = $item->addChild('enclosure');
                $enclosure->addAttribute('url', $mainURL.$post['blog_image_url']);
                $enclosure->addAttribute('length', '');
                $enclosure->addAttribute('type', 'image/png');
            }
        }
       
        // Save the RSS XML to a file
        $rssFeed->asXML($rssFilePath);

        // Return the generated RSS feed
        header('Content-Type: application/rss+xml; charset=utf-8');
        print $rssFeed->asXML();

        // Close database connection
        closeConnection($dbEngine, $db);
    }

} // End of BlogAPI Class

 
// Main Program Start

// Check if running from the command line
if (php_sapi_name() === 'cli') {

  // Parse command-line arguments
  $args = $argv;
  array_shift($args); 
  $endpoint = array_shift($args);

  // Check if we've got a valid endpoint
  $blogAPI = new BlogAPI();
  if(method_exists($blogAPI,$endpoint)) {

    // Prepare parameters
    $params = [];
    foreach ($args as $arg) {
      list($key, $val) = explode('=', $arg);
      $params[$key] = $val;
    }

    // Call endpoint
    $blogAPI->$endpoint($params);

  } else {

    // Provide command-line usage instructions
    print "Usage: php bloggable.php <endpoint> [parameters]".PHP_EOL;
    print "Available endpoints:".PHP_EOL;

    // List all the endpoints, excluding internal functions
    $class_methods = get_class_methods($blogAPI);
    foreach ($class_methods as $method_name) {
      if(!in_array($method_name, ['handleRequest','logAction','returnError','createJWT'])) {
        print $method_name.PHP_EOL;
      }
    }
  }
}


?>
