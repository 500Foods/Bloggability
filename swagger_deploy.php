#!/usr/bin/env php
<?php

require 'vendor/autoload.php';

// Generate base swagger.yaml
exec('./vendor/bin/openapi -l -o docs/swagger.yaml ~/public_html --exclude vendor');

// Load config
$json = json_decode(file_get_contents('bloggable.json'), true);

// Parse YAML
$yaml = Symfony\Component\Yaml\Yaml::parseFile('docs/swagger.yaml');

// Update info
$yaml['info']['title'] = $json['BLOGGABLE_TITLE'];
$yaml['info']['description'] = $json['BLOGGABLE_DESCRIPTION'];

// Dump YAML
file_put_contents('docs/swagger.yaml', Symfony\Component\Yaml\Yaml::dump($yaml));

print "swagger.yaml generated and updated!".PHP_EOL;
