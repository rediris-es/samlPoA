<?php

require_once __DIR__ . '/../vendor/autoload.php';

if (count($argv) != 2) {
    echo "Error. Syntax: $argv[0] settings_file.php\n";
    die(255);
}

$config = require_once $argv[1];
$poa = RedIRIS\SamlPoA\Builder::poa($config);

echo $poa->getMetadata();

