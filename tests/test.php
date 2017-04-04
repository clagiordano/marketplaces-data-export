<?php

namespace clagiordano\MarketplacesDataExport\Tests;

require __DIR__ . '/../vendor/autoload.php';

use clagiordano\MarketplacesDataExport\Adapters\Ebay;
use clagiordano\MarketplacesDataExport\Config;

$config = new Config(__DIR__ . '/../testdata/ebay.php');

$ebay = new Ebay($config);
print_r($ebay->getAppToken());

