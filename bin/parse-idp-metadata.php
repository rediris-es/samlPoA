<?php

require_once __DIR__ . '/../vendor/autoload.php';

if (count($argv) != 2) {
    echo "Error. Syntax: $argv[0] file.xml\n";
    die(255);
}

$metadata = \OneLogin_Saml2_IdPMetadataParser::parseFileXML($argv[1]);

echo "'idp' => ";
var_export($metadata['idp']);
echo ",\n";
