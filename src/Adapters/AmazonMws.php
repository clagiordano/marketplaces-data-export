<?php
/**
 * Created by PhpStorm.
 * User: claudio
 * Date: 30/01/18
 * Time: 15.10
 */

namespace clagiordano\MarketplacesDataExport\Adapters;

use clagiordano\MarketplacesDataExport\Config;
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
    public function getSellingTransactions($intervalStart = null, $intervalEnd = null)
    {
        if (!$intervalStart instanceof \DateTime || $intervalStart === null) {
            $intervalStart = new \DateTime();
        }

        $orders = $this->service->ListOrders(
            $intervalStart,
            true,
            [
                'Unshipped', 
                'PartiallyShipped'
            ]
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
                $currentTrData->shippingData->cost = $item['ShippingPrice']['Amount'];

                $transactions[$transaction['AmazonOrderId']][] = $currentTrData;
            }
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
     */
    public function completeSale(Transaction $trData, $shippingStatus = null, $feedbackMessage = null)
    {
//        $items = $this->service->ListOrderItems($trData->marketTransactionId);

//        $data = [
//            'OrderFulfillment' => [
//                'AmazonOrderID' => $trData->marketTransactionId,
//                'FulfillmentDate' => date('Y-m-d H:i:s'),
//                'OrderStatus' => $shippingStatus,
//                'MessageID' => rand(),
//            ]
//        ];
//
//        $response = $this->service->SubmitFeed(
//            '_POST_ORDER_FULFILLMENT_DATA_',
//            $data,
//            true
//        );
//
//        var_dump($response);
//        var_dump(simplexml_load_string($response));

//        var_dump($response['FeedSubmissionId']);
//        var_dump($this->service->GetFeedSubmissionResult($response['FeedSubmissionId']));
    }

    /**
     * @inheritDoc
     */
    public function getSellingList()
    {
        // TODO: Implement getSellingList() method.
    }

    /**
     * @inheritDoc
     */
    public function updateSellingProducts(array $products)
    {
        // TODO: Implement updateSellingProducts() method.
    }
}
