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

        $trData->shippingData->fulfillmentMethod = $this->getFulfillmentMethodByCode($transaction['FulfillmentChannel']);

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
    public function getSellingList($requestId = null, $fulfillmentType = null)
    {
        $reportType = '_GET_MERCHANT_LISTINGS_ALL_DATA_';
        if ($fulfillmentType === FulfillmentMethods::MARKETPLACE) {
            $reportType = '_GET_FBA_MYI_ALL_INVENTORY_DATA_';
        }

        if ($requestId === null) {
            /**
             * Submit new request for report
             */
            try {
                $requestId = (int)$this->service->RequestReport($reportType);
            } catch (\Exception $exception) {
                $requestId = 0;
            }

            return $requestId;
        }

        /**
         * Retrieve a requested report by requestId and returns a Product array
         */
        $products = [];
        try {
            $stocks = $this->service->GetReport($requestId);

            foreach ($stocks as $stock) {
                $product = new Product();

                if ($fulfillmentType === FulfillmentMethods::MARKETPLACE) {
                    $this->fbastockToProduct($stock, $product);
                } else {
                    $this->stockToProduct($stock, $product);
                }

                $products[] = $product;
            }
        } catch (\Exception $exception) {
            $products = null;
        }

        return $products;
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

    /**
     * Fulfillment method selection
     *
     * MFN / FBM: Merchant Fulfilled Network / "fulfillment by merchant"
     * FBA: Fulfillment by Amazon
     *
     * @param string $string Fulfillment method code
     * @return string
     */
    protected function getFulfillmentMethodByCode($string)
    {
        switch ($string) {
            case 'AFN':
            case 'FBA':
            case 'AMAZON_EU':
                $method = FulfillmentMethods::MARKETPLACE;
                break;

            case 'MFN':
            case 'FBM':
            case 'DEFAULT':
            default:
                $method =  FulfillmentMethods::SELLER;
                break;
        }

        return $method;
    }

    /**
     * Parse marketplace stock information for single product array and update Product.
     * @param array $data
     * @param Product $product
     */
    protected function stockToProduct(array $data, Product &$product)
    {
        $product->marketProductId = $data['listing-id'];
        $product->vendorProductId = $data['seller-sku'];
        $product->description = $data['item-name'];
        $product->storedAmount = $data['quantity'];
        $product->availableAmount = $data['quantity'];

        /**
         * Custom fields
         */
        $product->price = $data['price'];
        $product->fulfillmentChannel = $this->getFulfillmentMethodByCode($data['fulfillment-channel']);
        $product->status = $data['status'];
    }

    /**
     * Parse marketplace stock information for single product array and update Product.
     * @param array $data
     * @param Product $product
     */
    protected function fbastockToProduct(array $data, Product &$product)
    {
        $product->marketProductId = $data['sku'];
        $product->vendorProductId = $data['fnsku'];
        $product->description = $data['product-name'];
        $product->storedAmount = $data['afn-total-quantity'];
        $product->availableAmount = $data['afn-total-quantity'];

        /**
         * Custom fields
         */
        $product->price = $data['your-price'];
        $product->fulfillmentChannel = $this->getFulfillmentMethodByCode('FBA');
        $product->status = ($data['afn-listing-exists'] === 'Yes' ? 'Active' : 'Inactive');
    }
}
