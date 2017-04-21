<?php

namespace clagiordano\MarketplacesDataExport;

/**
 * Class Transaction
 * @package clagiordano\MarketplacesDataExport
 */
class Transaction
{
    /** @var Customer|null $customerData */
    public $customerData = null;
    /** @var Product|null $productData */
    public $productData = null;
    /** @var int|null $marketTransactionId */
    public $marketTransactionId = null;
    /** @var int|null $quantityPurchased */
    public $quantityPurchased = null;
    /** @var float|null $purchasePrice */
    public $purchasePrice = null;
    /** @var float|null $totalPrice */
    public $totalPrice = null;
    /** @var string|null  */
    public $paidTime = null;
    /** @var string|null $paymentStatus */
    public $paymentStatus = null;
    /** @var string|null $paymentMethod */
    public $paymentMethod = null;
    /** @var string|null $currency */
    public $currency = null;
    /** @var int|null $saleCounter */
    public $saleCounter = null;
    /** @var Shipping|null $shippingData */
    public $shippingData = null;
    /** @var string|null $customerNotes */
    public $customerNotes = null;
    /** @var string $transactionDate */
    public $transactionDate = null;

    /**
     * Transaction constructor.
     */
    public function __construct()
    {
        $this->customerData = new Customer();
        $this->productData = new Product();
        $this->shippingData = new Shipping();
    }
}
