<?php

$constantsFile = 'bloggable.json';
$constantsData = file_get_contents($constantsFile);
$constants = json_decode($constantsData, true);

/**
 * @OA\Info(
 *   version="1.0.1",
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

    }
  }

  // Logs all actions to the ACTIONS table
  public function logAction($appId, $ipAddress, $functionName, $weblogId) {
    // Check if SQLite3 extension is loaded
    if (!extension_loaded('sqlite3')) {
      echo "SQLite3 extension is not loaded. Please enable it in your PHP configuration.";
      return;
    }

    // Connect to SQLite database
    $db = new SQLite3($GLOBALS['constants']['Bloggable Database']);

    // Prepare and execute SQL statement to insert a new record
    $sql = "INSERT INTO ACTION (action_priority, action_source, weblog_id, action_ip_address, app_id) VALUES (?, ?, ?, ?, ?)";
    $stmt = $db->prepare($sql);
    $stmt->bindValue(1, 1, SQLITE3_INTEGER);
    $stmt->bindValue(2, $functionName, SQLITE3_TEXT);
    $stmt->bindValue(3, $weblogId, SQLITE3_TEXT);
    $stmt->bindValue(4, $ipAddress, SQLITE3_TEXT);
    $stmt->bindValue(5, $appId, SQLITE3_TEXT);
    $result = $stmt->execute();

    // Close database connection
    $db->close();

    return $result;
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
    $this->logAction($appId, $ipAddress, "Welcome", $weblogId);

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
      if(!in_array($method_name, ['handleRequest','logAction'])) {
        print $method_name . PHP_EOL;
      }
    }
  }
}

?>

