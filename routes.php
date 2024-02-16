<?php

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/api/', $uri)[1];
$method = $_SERVER['REQUEST_METHOD'];

if(!in_array($method, ['GET','POST'])) {
  header("HTTP/1.1 405 Method Not Allowed");
  exit;
}

require 'bloggable.php';
$blogAPI = new BlogAPI();
$blogAPI->handleRequest($uri, $method, $_GET, $_POST);

?>
