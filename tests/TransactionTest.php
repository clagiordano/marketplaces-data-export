<?php

namespace clagiordano\MarketplacesDataExport\Tests;

use clagiordano\MarketplacesDataExport\Transaction;

/**
 * Class TransactionTest
 * @package clagiordano\MarketplacesDataExport\Tests
 */
class TransactionTest extends \PHPUnit_Framework_TestCase
{
    /** @var Transaction $class */
    protected $class = null;

    protected function setUp()
    {
        $this->class = new Transaction();
        $this->assertInstanceOf('clagiordano\MarketplacesDataExport\Transaction', $this->class);
    }

    /**
     * @test
     */
    public function transactionHasCustomerData()
    {
        $this->assertObjectHasAttribute('customerData', $this->class);
    }

    /**
     * @test
     */
    public function transactionCustomerDataIsACustomer()
    {
        $this->assertInstanceOf('clagiordano\MarketplacesDataExport\Customer', $this->class->customerData);
    }

    /**
     * @test
     */
    public function transactionHasProductData()
    {
        $this->assertObjectHasAttribute('productData', $this->class);
    }

    /**
     * @test
     */
    public function transactionProductDataIsAProduct()
    {
        $this->assertInstanceOf('clagiordano\MarketplacesDataExport\Product', $this->class->productData);
    }

    /**
     * @test
     */
    public function transactionHasMarketTransactionId()
    {
        $this->assertObjectHasAttribute('marketTransactionId', $this->class);
    }

    /**
     * @test
     */
    public function transactionHasQuantityPurchased()
    {
        $this->assertObjectHasAttribute('quantityPurchased', $this->class);
    }

    /**
     * @test
     */
    public function transactionHasPurchasePrice()
    {
        $this->assertObjectHasAttribute('purchasePrice', $this->class);
    }

    /**
     * @test
     */
    public function transactionHasTotalPrice()
    {
        $this->assertObjectHasAttribute('totalPrice', $this->class);
    }

    /**
     * @test
     */
    public function transactionHasPaidTime()
    {
        $this->assertObjectHasAttribute('paidTime', $this->class);
    }

    /**
     * @test
     */
    public function transactionHasPaymentStatus()
    {
        $this->assertObjectHasAttribute('paymentStatus', $this->class);
    }

    /**
     * @test
     */
    public function transactionHasPaymentMethod()
    {
        $this->assertObjectHasAttribute('paymentMethod', $this->class);
    }

    /**
     * @test
     */
    public function transactionHasCurrency()
    {
        $this->assertObjectHasAttribute('currency', $this->class);
    }

    /**
     * @test
     */
    public function transactionHasSaleCounter()
    {
        $this->assertObjectHasAttribute('saleCounter', $this->class);
    }

    /**
     * @test
     */
    public function transactionHasShippingData()
    {
        $this->assertObjectHasAttribute('shippingData', $this->class);
    }

    /**
     * @test
     */
    public function transactionShippingDataIsAShipping()
    {
        $this->assertInstanceOf('clagiordano\MarketplacesDataExport\Shipping', $this->class->shippingData);
    }

    /**
     * @test
     */
    public function transactionHasCustomerNotes()
    {
        $this->assertObjectHasAttribute('customerNotes', $this->class);
    }

    /**
     * @test
     */
    public function transactionHasTransactionDate()
    {
        $this->assertObjectHasAttribute('transactionDate', $this->class);
    }

    /**
     * @test
     */
    public function transactionCanMagicSetProperty()
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
    public function transactionCanMagicGetProperty()
    {
        $property = "testProperty";
        $value = "test value";

        $this->class->{$property} = $value;

        $this->assertEquals($value, $this->class->{$property});
    }
}
