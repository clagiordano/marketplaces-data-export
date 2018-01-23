<?php

namespace clagiordano\MarketplacesDataExport\Tests;

use clagiordano\MarketplacesDataExport\Product;

/**
 * Class ProductTest
 * @package clagiordano\MarketplacesDataExport\Tests
 */
class ProductTest extends \PHPUnit_Framework_TestCase
{
    /** @var Product $class */
    protected $class = null;

    protected function setUp()
    {
        $this->class = new Product();
        $this->assertInstanceOf('clagiordano\MarketplacesDataExport\Product', $this->class);
    }

    /**
     * @test
     */
    public function productHasMarketProductId()
    {
        $this->assertObjectHasAttribute('marketProductId', $this->class);
    }

    /**
     * @test
     */
    public function productHasVendorProductId()
    {
        $this->assertObjectHasAttribute('vendorProductId', $this->class);
    }

    /**
     * @test
     */
    public function productHasDescription()
    {
        $this->assertObjectHasAttribute('description', $this->class);
    }

    /**
     * @test
     */
    public function productHasStoredAmount()
    {
        $this->assertObjectHasAttribute('storedAmount', $this->class);
    }

    /**
     * @test
     */
    public function productHasAvailableAmount()
    {
        $this->assertObjectHasAttribute('availableAmount', $this->class);
    }

    /**
     * @test
     */
    public function productHasIsVariation()
    {
        $this->assertObjectHasAttribute('isVariation', $this->class);
    }

    /**
     * @test
     */
    public function productCanMagicSetProperty()
    {
        $property = "testProperty";
        $value = "test value";

        $this->assertObjectNotHasAttribute($property, $this->class);

        $this->class->{$property} = $value;

        $this->assertObjectHasAttribute($property, $this->class);
    }

    /**
     * @test
     */
    public function productCanMagicGetProperty()
    {
        $property = "testProperty";
        $value = "test value";

        $this->class->{$property} = $value;

        $this->assertEquals($value, $this->class->{$property});
    }
}
