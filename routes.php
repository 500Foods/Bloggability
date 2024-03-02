<?php

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/api/', $uri)[1];
$method = $_SERVER['REQUEST_METHOD'];

if(!in_array($method, ['GET','POST'])) {
  header("HTTP/1.1 405 Method Not Allowed");
  exit;
}

require 'bloggability.php';
$blogAPI = new BlogAPI();

if (strpos($uri, 'rss-') === 0) {
  $weblogid = explode('.', $uri)[0];
  $weblogid = explode('-', $weblogid)[1];
  $blogAPI->handleRequest('rss', $method, ['weblogid' => $weblogid]);
} else {
  $blogAPI->handleRequest($uri, $method, $_GET, $_POST);
}

?>
