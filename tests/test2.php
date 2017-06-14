<?php

namespace clagiordano\MarketplacesDataExport\Tests;

require __DIR__ . '/../vendor/autoload.php';

use clagiordano\MarketplacesDataExport\Adapters\Ebay;
use clagiordano\MarketplacesDataExport\Config;
use clagiordano\MarketplacesDataExport\Product;

$config = new Config(__DIR__ . '/../testdata/ebay.php');

$ebay = new Ebay($config, false);

$list = $ebay->getSellingList();

$outFile = __DIR__ . '/../testdata/out/out_sample.csv';
$outLine = "Codice Prodotto;Descrizione;Quantità disponibile;Quantità\n";
file_put_contents($outFile, $outLine);

/** @var Product $prod */
foreach ($list as $prod) {
    $outLine = $prod->vendorProductId . ";";
    $outLine .= $prod->description . ";";
    $outLine .= $prod->availableAmount . ";";
    $outLine .= $prod->storedAmount . "\n";

    file_put_contents($outFile, $outLine, FILE_APPEND);
}

echo "Done!\n";