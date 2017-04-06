<?php

namespace clagiordano\MarketplacesDataExport;

use clagiordano\MarketplacesDataExport\CustomerData;

/**
 * Class Transaction
 * @package clagiordano\MarketplacesDataExport
 */
class TransactionData
{
//- mail
    /** @var CustomerData|null $customerData */
    public $customerData = null;
    /** @var null|int $marketProductId */
    public $marketProductId = null;
    /** @var int|null $marketTransactionId */
    public $marketTransactionId = null;
    /** @var string|null $vendorProductId */
    public $vendorProductId = null;
    /** @var int|null $quantityPurchased */
    public $quantityPurchased = null;
    /** @var float|null $purchasePrice */
    public $purchasePrice = null;
    /** @var bool $isSettled settled or not */
    public $isSettled = false;
    /** @var string|null $shippingAddress */
    public $shippingAddress = null;

}
