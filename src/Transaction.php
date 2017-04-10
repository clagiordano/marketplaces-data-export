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
    /** @var bool $isSettled settled or not */
    public $isSettled = false;
    /** @var string|null $sellerPaidStatus */
    public $sellerPaidStatus = null;
    /** @var float|null $totalPrice */
    public $totalPrice = null;
    /** @var string|null  */
    public $paidTime = null;
    /** @var string|null $currency */
    public $currency = null;
    /** @var int|null $saleCounter */
    public $saleCounter = null;
//    /** @var array $productList */
//    public $productList = [];

    public $shippingData = null;

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
