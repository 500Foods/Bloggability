#!/usr/bin/env php
<?php

require 'vendor/autoload.php';

// Generate base swagger.yaml
exec('vendor/bin/openapi -l -o docs/swagger.json ~/public_html --exclude vendor');

// Load Config JSON
$constants = json_decode(file_get_contents('bloggability.json'), true);

// Load Swagger JSON
$swagger = json_decode(file_get_contents('docs/swagger.json'), true);

// Perform updates
$swagger['info']['title'] = $constants['Bloggability Title'];
$swagger['info']['description'] = $constants['Bloggability Description'];

// Write out new JSON
file_put_contents('docs/swagger.json',json_encode($swagger, JSON_PRETTY_PRINT));

print "Swagger: swagger.yaml generated and updated!".PHP_EOL;
