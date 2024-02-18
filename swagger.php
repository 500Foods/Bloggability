#!/usr/bin/env php
<?php

require 'vendor/autoload.php';

// Generate base swagger.yaml
exec('vendor/bin/openapi -l -o docs/swagger.json ~/public_html --exclude vendor');

// Load Config JSON
$config = json_decode(file_get_contents('bloggable.json'), true);

// Load Swagger JSON
$swagger = json_decode(file_get_contents('docs/swagger.json'), true);

// Perform updates
$swagger['info']['title'] = $config['Bloggable Title'];
$swaggerl['info']['description'] = $config['Bloggable Description'];

// Write out new JSON
file_put_contents('docs/swagger.json',json_encode($swagger, JSON_PRETTY_PRINT));

print "Swagger: swagger.yaml generated and updated!".PHP_EOL;
