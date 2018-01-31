<?php
/**
 * Created by PhpStorm.
 * User: claudio
 * Date: 30/01/18
 * Time: 15.10
 */

namespace clagiordano\MarketplacesDataExport\Adapters;

use clagiordano\MarketplacesDataExport\Config;
use clagiordano\MarketplacesDataExport\Transaction;
use MCS\MWSClient;

/**
 * Class AmazonMws
 * @package clagiordano\MarketplacesDataExport\Adapters
 */
class AmazonMws extends AbstractAdapter
{
    /** @var MWSClient $service */
    protected $service = null;

    /**
     * Ebay constructor.
     * @param Config $config
     * @param bool $sandboxMode
     * @throws \Exception
     */
    public function __construct(Config $config, $sandboxMode = true)
    {
        parent::__construct($config, $sandboxMode);

        $section = 'sandbox';
        if (!$this->isSandboxMode) {
            $section = 'production';
        }

        $this->serviceConfig = [
            'Marketplace_Id' => $this->adapterConfig->getValue("{$section}.Marketplace_Id"),
            'Seller_Id' => $this->adapterConfig->getValue("{$section}.Seller_Id"),
            'Access_Key_ID' => $this->adapterConfig->getValue("{$section}.Access_Key_ID"),
            'Secret_Access_Key' => $this->adapterConfig->getValue("{$section}.Secret_Access_Key"),
            'MWSAuthToken' => $this->adapterConfig->getValue("{$section}.MWSAuthToken"),
        ];
        $this->service = new MWSClient($this->serviceConfig);
    }

    /**
     * @inheritDoc
     */
    public function getAppToken()
    {
        /**
         * Validate if token is set
         */
        if (!is_null($this->appToken)) {
            return $this->appToken;
        }

        if ($this->service->validateCredentials()) {
            $this->appToken = $this->serviceConfig['MWSAuthToken'];
            $this->appTokenExpireAt = strtotime("+ 7200 seconds");
        }

        return $this->appToken;
    }

    /**
     * @inheritDoc
     */
    public function getSellingTransactions($intervalStart = null, $intervalEnd = null)
    {
        if (!$intervalStart instanceof \DateTime || $intervalStart === null) {
            $intervalStart = new \DateTime();
        }

        $orders = $this->service->ListOrders(
            $intervalStart,
            true,
            [
                'Shipped'
            ]
        );

        $transactions = [];
        foreach ($orders as $transaction) {
            $transactions[] = $this->buildTransaction($transaction);
        }

        return $transactions;
    }

    /**
     * @param array $transaction
     * @return Transaction
     */
    protected function buildTransaction(array $transaction)
    {
        $trData = new Transaction();

        /**
         * Parse transaction data
         */
        $trData->marketTransactionId = $transaction['AmazonOrderId'];
        $trData->saleCounter = $transaction['AmazonOrderId'];
        $trData->quantityPurchased = $transaction['NumberOfItemsShipped'];

        /**
         * Parse shipping information
         */
        $trData->shippingData->contact = $transaction['ShippingAddress']['Name'];
        $trData->customerData->customerName = $transaction['ShippingAddress']['Name'];
        $trData->shippingData->address = $transaction['ShippingAddress']['AddressLine1'];
        $trData->shippingData->address .= " " . $transaction['ShippingAddress']['AddressLine2'];
        $trData->shippingData->cityName = $transaction['ShippingAddress']['City'];
        $trData->shippingData->stateOrProvince = $transaction['ShippingAddress']['StateOrRegion'];
        $trData->shippingData->countryCode = $transaction['ShippingAddress']['CountryCode'];
        $trData->shippingData->phone = $transaction['ShippingAddress']['Phone'];
        $trData->shippingData->postalCode = $transaction['PostalCode'];

//        $items = $this->service->ListOrderItems($transaction['AmazonOrderId']);

        return $trData;
    }
}
