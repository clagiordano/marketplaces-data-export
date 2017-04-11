<?php

namespace clagiordano\MarketplacesDataExport\Tests;

require __DIR__ . '/../vendor/autoload.php';

use clagiordano\MarketplacesDataExport\Adapters\Ebay;
use clagiordano\MarketplacesDataExport\Config;
use clagiordano\MarketplacesDataExport\Transaction;

$config = new Config(__DIR__ . '/../testdata/ebay.php');

$ebay = new Ebay($config, false);
//print_r($ebay->getAppToken());
//print_r($ebay->getSoldList());
$data = $ebay->getSoldListings();

//print_r($data);

$outFile = __DIR__ . '/../testdata/out/out_sample.csv';

$outLine = "Nome + cognome;Indirizzo;Cap;Comune;Provincia;Nazione;Email;Telefono;Nr.ordine web;Codice prodotto;Q.ta acquistata;Imponibile;Spese sped.\n";
file_put_contents($outFile, $outLine);

foreach ($data as $transaction) {
    foreach ($transaction as $item) {
        /** @var Transaction $item */

        // Nome + cognome
        $outLine = "{$item->shippingData->contact};";
        // Indirizzo
        $outLine .= "{$item->shippingData->address};";
        // Cap
        $outLine .= "{$item->shippingData->postalCode};";
        // Comune
        $outLine .= "{$item->shippingData->cityName};";
        // Provincia
        $outLine .= "{$item->shippingData->stateOrProvince};";
        // Nazione
        $outLine .= "{$item->shippingData->countryCode};";
        // Email
        $outLine .= "{$item->customerData->customerMail};";
        // Telefono
        $outLine .= "{$item->shippingData->phone};";
        // Nr. ordine web
        $outLine .= "{$item->saleCounter};";
        // codice prodotto
        $outLine .= "{$item->productData->vendorProductId};";
        // Q.ta acquistata
        $outLine .= "{$item->quantityPurchased};";
        // Imponibile
        $outLine .= "{$item->totalPrice};";
        // spese spedizione
        $outLine .= "{$item->shippingData->cost};";

        $outLine .= "\n";

//        var_dump($outLine);

        file_put_contents($outFile, $outLine, FILE_APPEND);
    }
}