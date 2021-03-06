<?php

namespace clagiordano\MarketplacesDataExport\Tests\Adapters;

use clagiordano\MarketplacesDataExport\Adapters\Ebay;
use clagiordano\MarketplacesDataExport\Config;
use clagiordano\MarketplacesDataExport\Transaction;
use PHPUnit\Framework\TestCase;

/**
 * Class EbayTest
 * @package Adapters
 */
class EbayTest extends TestCase
{
    /** @var Ebay $class */
    protected $class = null;

    public function setUp()
    {
        $configFile = __DIR__ . '/../../testdata/ebay.php';
        $this->assertFileExists($configFile);

        $config = new Config($configFile);
        $this->assertInstanceOf('clagiordano\MarketplacesDataExport\Config', $config);

        $this->class = new Ebay($config, false);
        $this->assertInstanceOf('clagiordano\MarketplacesDataExport\Adapters\Ebay', $this->class);
    }

    /**
     * @test
     */
    public function canGetAppToken()
    {
        $this->assertNotNull($this->class->getAppToken());
        $this->assertInternalType('string', $this->class->getAppToken());
    }

    /**
     * @test
     */
    public function canGetSellingTransactions()
    {
        $transactions = $this->class->getSellingTransactions(
            new \DateTime(date("Y-m-d", strtotime('-5 days'))),
            new \DateTime(date("Y-m-d"))
        );
        $this->assertInternalType('array', $transactions);
    }

    /**
     * @test
     * @group complete
     */
    public function canCompleteSale()
    {
        $transaction = new Transaction();
        $transaction->marketTransactionId = 'SAMPLE_TRANSACTION_ID';

        $this->class->completeSale(
            $transaction
        );
    }

    /**
     * @test
     */
    public function canGetSellingList()
    {
//        $response = $this->class->getSellingList();
//        var_dump(count($response));
    }

    /**
     * @test
     */
    public function canGetSellerList()
    {
        $this->markTestSkipped();
        $products = $this->class->getSellerList();

        $groups = [];
        foreach ($products as $product) {
            $groups[$product->country][] = $product;
        }

//        var_dump(count($groups));
//        var_dump(count($response));
    }
}
