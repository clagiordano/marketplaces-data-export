<?php

namespace clagiordano\MarketplacesDataExport\Tests;

use clagiordano\MarketplacesDataExport\Shipping;
use PHPUnit\Framework\TestCase;

/**
 * Class ShippingTest
 * @package clagiordano\MarketplacesDataExport\Tests
 */
class ShippingTest extends TestCase
{
    /** @var Shipping $class */
    protected $class = null;

    protected function setUp()
    {
        $this->class = new Shipping();
        $this->assertInstanceOf('clagiordano\MarketplacesDataExport\Shipping', $this->class);
    }

    /**
     * @test
     */
    public function shippingHasContact()
    {
        $this->assertObjectHasAttribute('contact', $this->class);
    }

    /**
     * @test
     */
    public function shippingHasAddress()
    {
        $this->assertObjectHasAttribute('address', $this->class);
    }

    /**
     * @test
     */
    public function shippingHasCityName()
    {
        $this->assertObjectHasAttribute('cityName', $this->class);
    }

    /**
     * @test
     */
    public function shippingHasStateOrProvince()
    {
        $this->assertObjectHasAttribute('stateOrProvince', $this->class);
    }

    /**
     * @test
     */
    public function shippingHasCountryCode()
    {
        $this->assertObjectHasAttribute('countryCode', $this->class);
    }

    /**
     * @test
     */
    public function shippingHasPhone()
    {
        $this->assertObjectHasAttribute('phone', $this->class);
    }

    /**
     * @test
     */
    public function shippingHasPostalCode()
    {
        $this->assertObjectHasAttribute('postalCode', $this->class);
    }

    /**
     * @test
     */
    public function shippingHasPhone2()
    {
        $this->assertObjectHasAttribute('phone2', $this->class);
    }

    /**
     * @test
     */
    public function shippingHasStatus()
    {
        $this->assertObjectHasAttribute('status', $this->class);
    }

    /**
     * @test
     */
    public function shippingHasCost()
    {
        $this->assertObjectHasAttribute('cost', $this->class);
    }

    /**
     * @test
     */
    public function shippingCanMagicSetProperty()
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
    public function shippingCanMagicGetProperty()
    {
        $property = "testProperty";
        $value = "test value";

        $this->class->{$property} = $value;

        $this->assertEquals($value, $this->class->{$property});
    }
}
