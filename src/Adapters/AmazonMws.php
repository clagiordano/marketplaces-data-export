<?php

namespace clagiordano\MarketplacesDataExport\Adapters;

use clagiordano\MarketplacesDataExport\Config;
use clagiordano\MarketplacesDataExport\FulfillmentMethods;
use clagiordano\MarketplacesDataExport\Product;
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
    public function getSellingTransactions(
        $intervalStart = null,
        $intervalEnd = null,
        $shipmentStates = [
            'Unshipped',
            'PartiallyShipped'
        ],
        $fulfillmentChannel = 'MFN'
    ) {
        if (!$intervalStart instanceof \DateTime || $intervalStart === null) {
            $intervalStart = new \DateTime();
        }

        $orders = $this->service->ListOrders(
            $intervalStart,
            true,
            $shipmentStates,
            $fulfillmentChannel
        );

        $transactions = [];
        foreach ($orders as $transaction) {
            $trData = $this->buildTransaction($transaction);

            $items = $this->service->ListOrderItems($transaction['AmazonOrderId']);
            foreach ($items as $item) {
                $currentTrData = clone $trData;

                $currentTrData->productData = $this->itemToProduct($item);

                $currentTrData->quantityPurchased = $item['QuantityOrdered'];
                $currentTrData->purchasePrice = $item['ItemPrice']['Amount'];
                $currentTrData->currency = $transaction['OrderTotal']['CurrencyCode'];
                $currentTrData->totalPrice = (float)($currentTrData->purchasePrice * $currentTrData->quantityPurchased);
                if (isset($item['ShippingPrice'])) {
                    $currentTrData->shippingData->cost = $item['ShippingPrice']['Amount'];
                }

                $transactions[$transaction['AmazonOrderId']][] = $currentTrData;
            }

            sleep(2);
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
        $trData->totalPrice = $transaction['OrderTotal']['Amount'];
        $trData->currency = $transaction['OrderTotal']['CurrencyCode'];
        $trData->transactionDate = $transaction['PurchaseDate'];
        $trData->paidTime = $transaction['PurchaseDate'];
        $trData->paymentMethod = $transaction['PaymentMethod'];
        $trData->paymentMethod .= " ({$transaction['PaymentMethodDetails']['PaymentMethodDetail']})";
        $trData->paymentStatus = "";
        $trData->customerNotes = "";

        /**
         * Parse shipping information
         */
        if (isset($transaction['ShippingAddress']['Name'])) {
            $trData->shippingData->contact = $transaction['ShippingAddress']['Name'];
        }

        if (isset($transaction['ShippingAddress']['AddressLine1'])) {
            $trData->shippingData->address = $transaction['ShippingAddress']['AddressLine1'];
        }

        if (isset($transaction['ShippingAddress']['AddressLine2'])) {
            $trData->shippingData->address .= " " . $transaction['ShippingAddress']['AddressLine2'];
        }

        if (isset($transaction['ShippingAddress']['City'])) {
            $trData->shippingData->cityName = $transaction['ShippingAddress']['City'];
        }

        if (isset($transaction['ShippingAddress']['StateOrRegion'])) {
            $trData->shippingData->stateOrProvince = $transaction['ShippingAddress']['StateOrRegion'];
        }

        if (isset($transaction['ShippingAddress']['CountryCode'])) {
            $trData->shippingData->countryCode = $transaction['ShippingAddress']['CountryCode'];
        }

        if (isset($transaction['ShippingAddress']['Phone'])) {
            $trData->shippingData->phone = $transaction['ShippingAddress']['Phone'];
        }

        if (isset($transaction['ShippingAddress']['PostalCode'])) {
            $trData->shippingData->postalCode = $transaction['ShippingAddress']['PostalCode'];
        }

        $trData->shippingData->status = $transaction['OrderStatus'];

        /**
         * Fulfillment method selection
         *
         * MFN / FBM: Merchant Fulfilled Network / "fulfillment by merchant"
         * FBA: Fulfillment by Amazon
         */
        if (isset($transaction['FulfillmentChannel'])) {
            switch ($transaction['FulfillmentChannel']) {
                case 'AFN':
                case 'FBA':
                    $trData->shippingData->fulfillmentMethod = FulfillmentMethods::MARKETPLACE;
                    break;

                case 'MFN':
                case 'FBM':
                default:
                    $trData->shippingData->fulfillmentMethod = FulfillmentMethods::SELLER;
                    break;
            }

        }

        /**
         * Parse customer data
         */
        $trData->customerData->customerName = $transaction['ShippingAddress']['Name'];
        $trData->customerData->customerSurame = "";
        $trData->customerData->billingAddress = $trData->shippingData->address;
        $trData->customerData->customerMail = $transaction['BuyerEmail'];
        $trData->customerData->userId = $transaction['BuyerName'];
        $trData->customerData->country = $trData->shippingData->countryCode;
        $trData->customerData->postalCode = $trData->shippingData->postalCode;

        return $trData;
    }

    /**
     * Returns a Product from an MWS order item
     *
     * @param array $item
     * @return Product;
     */
    protected function itemToProduct(array $item)
    {
        $product = new Product();

        $product->description = $item['Title'];
        $product->marketProductId = $item['OrderItemId'];
        $product->vendorProductId = $item['SellerSKU'];
        $product->availableAmount = $item['QuantityShipped'];
        $product->storedAmount = $item['ProductInfo']['NumberOfItems'];

        return $product;
    }

    /**
     * @inheritDoc
     *
     * @param string $carrierName
     * @param string $shippingMethod
     * @param string $shippingTracking
     */
    public function completeSale(
        Transaction $trData,
        $carrierName = null,
        $shippingMethod = null,
        $shippingTracking = null
    )
    {
        $feed = [
            'MessageType' => 'OrderFulfillment',
            'Message' => [
                'MessageID' => rand(),
                'OperationType' => 'Update',
                'OrderFulfillment' => [
                    'AmazonOrderID' => $trData->marketTransactionId,
                    'FulfillmentDate' => gmdate("Y-m-d\TH:i:s.\\0\\0\\0\\Z", time()),
                    'FulfillmentData' => [
                        'CarrierName' => $carrierName,
                        'ShippingMethod' => $shippingMethod,
                        'ShipperTrackingNumber' => $shippingTracking
                    ]
                ]
            ]
        ];

        $response = $this->service->SubmitFeed(
            '_POST_ORDER_FULFILLMENT_DATA_',
            $feed
        );

        return ($response['FeedProcessingStatus'] === '_SUBMITTED_' ? true : false);
    }

    /**
     * @inheritDoc
     */
    public function getSellingList()
    {
        try {
            $requestId = (int)$this->service->RequestReport(
                '_GET_MERCHANT_LISTINGS_DATA_BACK_COMPAT_'
            );
        } catch (\Exception $exception) {
            $requestId = 0;
        }

        return $requestId;
    }

    /**
     * @inheritDoc
     */
    public function updateSellingProducts(array $products)
    {
        $updates = [];

        /** @var Product $product */
        foreach ($products as $product) {
            $updates[$product->vendorProductId] = $product->availableAmount;
        }

        $response = $this->service->updateStock($updates);

        return ($response['FeedProcessingStatus'] === '_SUBMITTED_' ? true : false);
    }
}
