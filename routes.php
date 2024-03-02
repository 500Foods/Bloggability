<?php

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$api_uri = explode('/api/', $uri)[1];
$method = $_SERVER['REQUEST_METHOD'];

if(!in_array($method, ['GET','POST'])) {
  header("HTTP/1.1 405 Method Not Allowed");
  exit;
}

require 'bloggability.php';
$blogAPI = new BlogAPI();

if (strpos($uri, '/rss-') === 0) {
  $weblog = explode('.', $uri)[0];
  $weblog = explode('-', $weblog)[1];
  $blogAPI->handleRequest('RSS', $method, ['weblog' => $weblog]);

} else {
  $blogAPI->handleRequest($api_uri, $method, $_GET, $_POST);
}

?>
