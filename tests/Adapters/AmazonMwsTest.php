<?php

namespace clagiordano\MarketplacesDataExport\Tests\Adapters;

use clagiordano\MarketplacesDataExport\Adapters\AmazonMws;
use clagiordano\MarketplacesDataExport\Config;
use clagiordano\MarketplacesDataExport\Product;
use clagiordano\MarketplacesDataExport\Transaction;

/**
 * Class AmazonMwsTest
 * @package Adapters
 */
class AmazonMwsTest extends \PHPUnit_Framework_TestCase
{
    /** @var AmazonMws $class */
    protected $class = null;

    public function setUp()
    {
        $configFile = __DIR__ . '/../../testdata/mws.php';
        $this->assertFileExists($configFile);

        $config = new Config($configFile);
        $this->assertInstanceOf('clagiordano\MarketplacesDataExport\Config', $config);

        $this->class = new AmazonMws($config, false);
        $this->assertInstanceOf('clagiordano\MarketplacesDataExport\Adapters\AmazonMws', $this->class);
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
            new \DateTime(date("Y-m-d", strtotime('-1 days'))),
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
            $transaction,
            'SampleCarrier',
            'SampleMethod'
        );
    }

    /**
     * @test
     * @group sellinglist
     */
    public function canGetSellingList()
    {
//        $this->class->test();
//        $this->markTestIncomplete();
//        $products = $this->class->getSellingList();
//        var_dump(count($products));
    }

    /**
     * @test
     * @group update
     */
    public function canUpdateSellingProducts()
    {
        $prod = new Product();
        $prod->vendorProductId = 'TEST_SKU';
        $prod->availableAmount = 5;
        $updates[] = $prod;

        $this->class->updateSellingProducts($updates);
    }
}
