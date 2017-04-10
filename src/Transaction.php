<?php

namespace clagiordano\MarketplacesDataExport;

use clagiordano\MarketplacesDataExport\Customer;
use clagiordano\MarketplacesDataExport\Product;

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
    /** @var string|null $shippingAddress */
    public $shippingAddress = null;
    /** @var float|null $totalPrice */
    public $totalPrice = null;
    /** @var string|null  */
    public $paidTime = null;
    /** @var string|null $currency */
    public $currency = null;

    /**
     * Transaction constructor.
     */
    public function __construct()
    {
        $this->customerData = new Customer();
        $this->productData = new Product();
    }
}
