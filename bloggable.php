<?php

# This is needed for generating the actual JWT
require 'vendor/autoload.php';
use Firebase\JWT\JWT;

# Globals are stored in a JSON file so they can be used by other programs
$constantsFile = 'bloggable.json';
$constantsData = file_get_contents($constantsFile);
$constants = json_decode($constantsData, true);

/**
 * @OA\Info(
 *   version="1.0.3",
 *   title="Bloggable", 
 *   description="REST API for bloggable service",
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
    switch($uri) {
      case 'welcome':
        $this->Welcome($params);
        break;
      case 'login':
        $this->Login($params);
        break;

    }
  } 

 
  // Creates a JWT with whatever claims we need to be included
  private function createJWT($jwtSecret, $accountId, $appId, $weblogId, $accountStatus, $accountName, $accountEmail, $weblogs, $authors) {
    $issuedAt = new DateTimeImmutable();
    $expire = $issuedAt->modify('+1 day')->getTimestamp();

    $jwt = [
      'iat' => $issuedAt->getTimestamp(),
      'exp' => $expire,
      'iss' => 'bloggable',
      'aud' => 'bloggable',
      'acc_id' => $accountId,
      'app_id' => $appId,    
      'web_id' => $weblogId, 
      'acc_status' => $accountStatus,
      'acc_name' => $accountName,
      'acc_email' => $accountEmail,
      'weblogs' => $weblogs,
      'authors' => $authors
    ];

    return JWT::encode($jwt, $jwtSecret,'HS256');
  }


  // Logs all actions to the ACTIONS table
  private function logAction($appId, $ipAddress, $functionName, $weblogId, $message, $startTime) {

    // Check if SQLite3 extension is loaded
    if (!extension_loaded('sqlite3')) {
      echo "SQLite3 extension is not loaded. Please enable it in your PHP configuration.";
      return;
    }

    // Connect to SQLite database
    $db = new SQLite3($GLOBALS['constants']['Bloggable Database']);

    // Calculate execution_time from $startTime
    $executionTime = round(microtime(true) - $startTime, 3) * 1000;

    // Prepare and execute SQL statement to insert a new record
    $sql = "INSERT INTO ACTION (action_priority, action_source, action_weblog_id, action_ip_address, action_app_id, action_execution_time, action_description) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $db->prepare($sql);
    $stmt->bindValue(1, 1, SQLITE3_INTEGER);
    $stmt->bindValue(2, $functionName, SQLITE3_TEXT);
    $stmt->bindValue(3, $weblogId, SQLITE3_TEXT);
    $stmt->bindValue(4, $ipAddress, SQLITE3_TEXT);
    $stmt->bindValue(5, $appId, SQLITE3_TEXT);
    $stmt->bindValue(6, $executionTime, SQLITE3_INTEGER);
    $stmt->bindValue(7, $message, SQLITE3_TEXT);
    $result = $stmt->execute();

    // Close database connection
    $db->close();

    return $result;
  }

  // Returns an error if something was encountered while executing an endpoint
  private function returnError($endpoint, $error, $ipAddress, $appId, $accountId, $weblogId, $startTime) {
    // Log the error
    $message = "$endpoint failed due to $error";
    $this->logAction($appId, $ipAddress, $endpoint, $weblogId, $message, $startTime);

    // Return error response
    header('Content-Type: application/json');
    print json_encode(['Status' => "Error $error"]) . PHP_EOL;
  }


  /**
   * @OA\Get(
   *   path="/api/login",
   *   summary="Login",
   *   @OA\Parameter(name="apikey",    in="query", required=true, @OA\Schema(type="string")),
   *   @OA\Parameter(name="email",     in="query", required=true, @OA\Schema(type="string")),
   *   @OA\Parameter(name="password",  in="query", required=true, @OA\Schema(type="string")),
   *   @OA\Parameter(name="weblog_id", in="query", required=true, @OA\Schema(type="string")),
   *   @OA\Response(response=200, description="Successful login"),
   *   @OA\Response(response=400, description="Validation error")
   * )
   **/

  public function Login($params) {
    // Start timer
    $startTime = microtime(true);
  
    // Default values (for logging purposes initially)
    $accountId = 'N/A';
    $appId = 'N/A';

    // Set IP address
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'localhost: console';

    // Extract parameters
    $apikey = $params['apikey'] ?? null;
    $email = $params['email'] ?? null;
    $password = $params['password'] ?? null;
    $weblogId = $params['weblog_id'] ?? null;
    
    // Check that we've got all the parameters that we need to proceed
    if (!$apikey || !$email || !$password || !$weblogId) {
      $this->returnError('Login', 'Login failed: Incomplete credentials', $ipAddress, $appId, $accountId, $weblogId, $startTime);
    }

    // Connect to database
    $db = new SQLite3($GLOBALS['constants']['Bloggable Database']);
    
    // Validate APIKEY
    $sql = "SELECT apikey, app_id FROM APIKEY WHERE apikey = ? AND issued_at < current_timestamp AND expires_at > current_timestamp";
    $stmt = $db->prepare($sql);
    $stmt->bindValue(1, $apikey, SQLITE3_TEXT);
    $result = $stmt->execute();
    $validKey = $result->fetchArray();

    // Check if APIKEY is valid
    if (!$validKey) {
      $this->returnError("Login", "Login failed: Invalid APIKEY", $ipAddress, $appId, $accountId, $weblogId, $startTime);
    }

    // Get app_id
    $appId = $validKey['app_id'];

    // Check Account
    $sql = "SELECT account_id, account_name, account_email, account_password_hash, account_status
              FROM ACCOUNT WHERE account_email = ?";
    $stmt = $db->prepare($sql);
    $stmt->bindValue(1, $email, SQLITE3_TEXT);
    $result = $stmt->execute();
    $account = $result->fetchArray(SQLITE3_ASSOC);

    if(!$account){
      $this->returnError("Login", "Login failed: E-Mail address not found.", $ipAddress, $appId, $accountId, $weblogId, $startTime);
    }

    // Get account info
    $passwordHash = $account['account_password_hash'];
    $accountId = $account['account_id'];
    $accountStatus = $account['account_status'];
    $accountName = $account['account_name'];
    $accountEmail = $account['account_email'];
    
    // Account disabled
    if ($accountStatus = 0) {
      $this->returnError("Login", "Login failed: Account disabled.", $ipAddress, $appId, $accountId, $weblogId, $startTime);
    }

    // Verify password
    global $constants;
    $secret = $constants['Bloggable JWT Secret'];
    $hash = hash('sha256', $secret . $accountId . $password);
    if ($hash != $passwordHash) {
      $this->returnError("Login", "Login failed: Invalid password.", $ipAddress, $appId, $accountId, $weblogId, $startTime);
    }
  
    // Get weblog access info
    $sql = "SELECT * FROM WEBLOG_ACCOUNT WHERE account_id = ?";
    $stmt = $db->prepare($sql);
    $stmt->bindValue(1, $accountId, SQLITE3_TEXT);
    $result = $stmt->execute();
    $weblogs = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
      $weblogId = $row['weblog_id'];
      $weblogRole = $row['weblog_account_role'];
      $weblogs[$weblogId] = $weblogRole;
    }
  
    // Get author access info
    $sql = "SELECT * FROM AUTHOR_ACCOUNT WHERE account_id = ?";
    $stmt = $db->prepare($sql);
    $stmt->bindValue(1, $accountId, SQLITE3_TEXT);
    $result = $stmt->execute();
    $authors = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
      $authorId = $row['author_id'];
      $authorStatus = $row['author_account_status'];
      $authors[$authorId] = $authorStatus;
    }
  
    // Create JWT with claims including weblogs and authors

    $jwt = $this->createJWT($secret, $accountId, $appId, $weblogId, $accountStatus, $accountName, $accountEmail, $weblogs, $authors);

    // Store the JWT in the database
    $sql = "INSERT INTO TOKEN (token, issued_by, issued_at, expires_at, issued_for) VALUES (?, ?, current_timestamp, ?, ?)";
    $stmt = $db->prepare($sql);
    $stmt->bindValue(1, $jwt, SQLITE3_TEXT);
    $stmt->bindValue(2, $appId, SQLITE3_TEXT);
    $stmt->bindValue(3, date('Y-m-d H:i:s', strtotime('+1 day')), SQLITE3_TEXT);
    $stmt->bindValue(4, $accountId, SQLITE3_TEXT);
    $result = $stmt->execute();

    // Log successful login
    $message = "Successful login for $email";
    $this->logAction($appId, $ipAddress, 'Login', $weblogId, $message, $startTime);
	
    // Return success response
    header('Content-Type: application/json');
    $response = [
      'Status' => 'Success',
      'jwt' => $jwt,
      'email' => $email,
      'name' => $account['account_name'],
      'account_id' => $accountId,
      'expires_at' => date('Y-m-d H:i:s', strtotime('+1 day')),
      'weblog_ids' => $weblogId,
      'permissions' => 'admin'  // Hardcoded for now
    ];
    print json_encode($response);

    // Close database connection
    $db->close();
  }



  /**
   * @OA\Get(
   *   path="/api/welcome",
   *   tags={"Blog"},
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

}


// Main Program Start

// Check if running from the command line
if (php_sapi_name() === 'cli') {

  // Parse command-line arguments
  $args = $argv;
  array_shift($args); // Remove the script name
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
    print "Usage: php bloggable.php <endpoint> [parameters]" . PHP_EOL;
    print "Available endpoints:" . PHP_EOL;

    // List all the endpoints, excluding internal functions
    $class_methods = get_class_methods($blogAPI);
    foreach ($class_methods as $method_name) {
      if(!in_array($method_name, ['handleRequest','logAction','returnError','createJWT'])) {
        print $method_name . PHP_EOL;
      }
    }
  }
} 

?>

