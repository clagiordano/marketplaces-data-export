<?php

namespace clagiordano\MarketplacesDataExport\Tests;

use clagiordano\MarketplacesDataExport\Customer;
use PHPUnit\Framework\TestCase;

/**
 * Class CustomerTest
 * @package clagiordano\MarketplacesDataExport\Tests
 */
class CustomerTest extends TestCase
{
    /** @var Customer $class */
    protected $class = null;

    protected function setUp()
    {
        $this->class = new Customer();
        $this->assertInstanceOf('clagiordano\MarketplacesDataExport\Customer', $this->class);
    }

    /**
     * @test
     */
    public function customerHasCustomerName()
    {
        $this->assertObjectHasAttribute('customerName', $this->class);
    }

    /**
     * @test
     */
    public function customerHasCustomerSurame()
    {
        $this->assertObjectHasAttribute('customerSurame', $this->class);
    }

    /**
     * @test
     */
    public function customerHasBillingAddress()
    {
        $this->assertObjectHasAttribute('billingAddress', $this->class);
    }

    /**
     * @test
     */
    public function customerHasCustomerMail()
    {
        $this->assertObjectHasAttribute('customerMail', $this->class);
    }

    /**
     * @test
     */
    public function customerHasUserId()
    {
        $this->assertObjectHasAttribute('userId', $this->class);
    }

    /**
     * @test
     */
    public function customerHasCountry()
    {
        $this->assertObjectHasAttribute('country', $this->class);
    }

    /**
     * @test
     */
    public function customerHasPostalCode()
    {
        $this->assertObjectHasAttribute('postalCode', $this->class);
    }

    /**
     * @test
     */
    public function customerCanMagicSetProperty()
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
    public function customerCanMagicGetProperty()
    {
        $property = "testProperty";
        $value = "test value";

        $this->class->{$property} = $value;

        $this->assertEquals($value, $this->class->{$property});
    }
}
