<?php

namespace clagiordano\MarketplacesDataExport\Tests;

require __DIR__ . '/../vendor/autoload.php';

use clagiordano\MarketplacesDataExport\Config;

$config = new Config(__DIR__ . '/../testdata/ebay.php');
var_dump($config->getValue('sandbox'));