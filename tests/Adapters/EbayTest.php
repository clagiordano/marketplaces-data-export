<?php

namespace clagiordano\MarketplacesDataExport\Tests\Adapters;

use clagiordano\MarketplacesDataExport\Adapters\Ebay;
use clagiordano\MarketplacesDataExport\Config;

/**
 * Class EbayTest
 * @package Adapters
 */
class EbayTest extends \PHPUnit_Framework_TestCase
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
    public function canGetSoldListings()
    {
        $response = $this->class->getSoldListings(new \DateTime(), new \DateTime());
//        var_dump(count($response));
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
        $products = $this->class->getSellerList();

        $groups = [];
        foreach ($products as $product) {
            $groups[$product->country][] = $product;
        }

//        var_dump(count($groups));
//        var_dump(count($response));
    }
}
