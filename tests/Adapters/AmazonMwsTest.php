<?php

namespace clagiordano\MarketplacesDataExport\Tests\Adapters;

use clagiordano\MarketplacesDataExport\Adapters\AmazonMws;
use clagiordano\MarketplacesDataExport\Config;
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
        $transaction->marketTransactionId = '404-6409497-0982735';
        $transaction->productData->marketProductId = '';

        $this->class->completeSale($transaction, 'Success');
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
