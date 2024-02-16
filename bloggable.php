<?php

$constantsFile = 'bloggable.json';
$constantsData = file_get_contents($constantsFile);
$constants = json_decode($constantsData, true);

/**
 * @OA\Info(
 *      version="1.0.1",
 *      title="Bloggable",
 *      description="REST API for bloggable service",
 * )
 */

class BlogAPI {

  private function LogAction($appId, $ipAddress, $functionName, $weblogId) {
    // Check if SQLite3 extension is loaded
    if (!extension_loaded('sqlite3')) {
      echo "SQLite3 extension is not loaded. Please enable it in your PHP configuration.";
      return;
    }

    // Connect to SQLite database
    $db = new SQLite3($GLOBALS['constants']['BLOGGABLE_DATABASE']);

    // Prepare and execute SQL statement to insert a new record
    $sql = "INSERT INTO ACTIONS (action_id, action_description, action_timestamp, action_address) VALUES (NULL, ?, CURRENT_TIMESTAMP, ?)";
    $stmt = $db->prepare($sql);
    $stmt->bindValue(1, $functionName, SQLITE3_TEXT);
    $stmt->bindValue(2, $ipAddress, SQLITE3_TEXT);
    $result = $stmt->execute();

    // Close database connection
    $db->close();

    return $result;
  }

  /**
   * @OA\Get(
   *     path="/welcome",
   *     tags={"Blog"},
   *     summary="Get blog welcome data",
   *     @OA\Parameter(
   *         name="weblog_id",
   *         in="query",
   *         required=false,
   *         description="Weblog ID",
   *         @OA\Schema(
   *             type="string",
   *             default="web001"
   *         )
   *     ),
   *     @OA\Response(
   *         response=200,
   *         description="Successful response",
   *     ),
   * )
   */

  // Other functions and endpoints...

  public function Welcome($weblogId = 'web001') {
    // Check if SQLite3 extension is loaded
    if (!extension_loaded('sqlite3')) {
        print "SQLite3 extension is not loaded. Please enable it in your PHP configuration." . PHP_EOL;
        return;
    }

    // Set the IP address based on the environment
    $ipAddress = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'localhost: console';

    // Log the action
    $this->LogAction("appId", $ipAddress, "Welcome", $weblogId);

    // Connect to SQLite database
    $db = new SQLite3($GLOBALS['constants']['BLOGGABLE_DATABASE']);

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

    // Check if multiple rows were returned
    if (count($weblogData) > 1) {
        http_response_code(500);
        print "Error: Multiple rows returned for weblog_id: $weblogId" . PHP_EOL;
        return;
    }

    // Close database connection
    $db->close();

    // Return the data as JSON
    header('Content-Type: application/json');
    print json_encode($weblogData) . PHP_EOL;
}

}

// Check if running from the command line
if (php_sapi_name() === 'cli') {
    // Parse command-line arguments
    $args = $argv;
    array_shift($args); // Remove the script name
    $endpoint = array_shift($args);

    if ($endpoint === 'welcome') {
        $weblogId = null;
        foreach ($args as $arg) {
            if (strpos($arg, 'weblog_id=') === 0) {
                $weblogId = substr($arg, strlen('weblog_id='));
                break;
            }
        }

        $blogAPI = new BlogAPI();
        $blogAPI->Welcome($weblogId);
    } else {
        print "Usage: php bloggable.php <endpoint> [parameters]" . PHP_EOL;
        print "Available endpoints: welcome" . PHP_EOL;
    }
} else {
    // Handle web requests through routes.php
}

?>
