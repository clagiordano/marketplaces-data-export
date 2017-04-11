<?php

namespace clagiordano\MarketplacesDataExport\Tests;

require __DIR__ . '/../vendor/autoload.php';

use clagiordano\MarketplacesDataExport\Adapters\Ebay;
use clagiordano\MarketplacesDataExport\Config;

$config = new Config(__DIR__ . '/../testdata/ebay.php');

$ebay = new Ebay($config, false);
//print_r($ebay->getAppToken());
//print_r($ebay->getSoldList());
$data = $ebay->getSoldListings();

$outFile = __DIR__ . '/../testdata/out/out_sample.csv';

foreach ($data as $transaction) {

}