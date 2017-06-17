<?php

namespace clagiordano\MarketplacesDataExport\Tests;

require __DIR__ . '/../vendor/autoload.php';

use clagiordano\MarketplacesDataExport\Adapters\Ebay;
use clagiordano\MarketplacesDataExport\Config;
use clagiordano\MarketplacesDataExport\Product;

$config = new Config(__DIR__ . '/../testdata/ebay.php');

$ebay = new Ebay($config, false);
$items = $ebay->getSellingList();
$testItem = null;
foreach ($items as $product) {
//    if (stripos($product->description, "dive") > 0) {
//        print_r($product);
//        break;
//    }
    if ($product->vendorProductId == "JVA943002") {
        print_r($product);
        $testItem = $product;
        break;
    }
}

$product->availableAmount = 12;

$ebay->reviseInventoryStatus([$product]);

$items = $ebay->getSellingList();
foreach ($items as $product) {
    if ($product->vendorProductId == "JVA943002") {
        print_r($product);
        break;
    }
}

echo "Done!\n";