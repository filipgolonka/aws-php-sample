<?php

require 'vendor/autoload.php';

$config = require_once('config.php');
if (!$config || !is_array($config)) {
    throw new \Exception('Can not use client without $config');
}

require 'VersionsRemover.php';

$versionsRemover = new VersionsRemover($config);
$result = $versionsRemover->perform();

echo $result . "\n";
return $result;
