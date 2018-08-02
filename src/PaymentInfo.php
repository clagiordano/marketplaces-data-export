<?php

namespace clagiordano\MarketplacesDataExport;

/**
 * Class PaymentInfo
 * @package clagiordano\MarketplacesDataExport
 */
class PaymentInfo
{
    /** @var string|null */
    public $status = null;
    /** @var string|null */
    public $method = null;
    /** @var bool $isExternal */
    public $isExternal = false;
    /** @var null $externalTransactionID */
    public $externalPaymentId = null;
    /** @var bool $externalPaymentStatus */
    public $externalPaymentStatus = false;
    /** @var null|float $externalPaymentFee */
    public $externalPaymentFee = null;
    /** @var null|string $externalPaymentFeeCurrency */
    public $externalPaymentFeeCurrency = null;
}
