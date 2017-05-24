<?php

namespace clagiordano\MarketplacesDataExport\Adapters;

use clagiordano\MarketplacesDataExport\Config;
use clagiordano\MarketplacesDataExport\Transaction;
use DTS\eBaySDK\Merchandising\Types\Item;
use \DTS\eBaySDK\OAuth\Services;
use \DTS\eBaySDK\Constants\SiteIds;
use \DTS\eBaySDK\Trading\Services\TradingService;
use \DTS\eBaySDK\Trading\Types;
use \DTS\eBaySDK\Trading\Enums;
use \DateTime;

/**
 * Class Ebay
 * @package clagiordano\MarketplacesDataExport\Adapters
 */
class Ebay extends AbstractAdapter
{
    protected $service = null;
    /** @var array $serviceConfig */
    protected $serviceConfig = [];
    /** @var bool $isSandboxMode */
    protected $isSandboxMode = true;
    /** @var string $appToken */
    protected $appToken = null;
    /** @var int $appTokenExpireAt */
    protected $appTokenExpireAt = 0;
    /** @var null|TradingService $tradingService */
    protected $tradingService = null;

    /**
     * Ebay constructor.
     * @param Config $config
     * @param bool $sandboxMode
     */
    public function __construct(Config $config, $sandboxMode = true)
    {
        parent::__construct($config);

        $this->isSandboxMode = $sandboxMode;

        $section = 'sandbox';
        if (!$this->isSandboxMode) {
            $section = 'production';
        }

        $this->serviceConfig = [
            'credentials' => $this->adapterConfig->getValue("{$section}.credentials"),
            'ruName'      => $this->adapterConfig->getValue("{$section}.ruName"),
            'siteId'      => SiteIds::IT,   // TODO migrate to config file
            'sandbox'     => $this->isSandboxMode,
//            'apiVersion'  => "997",
        ];
        $this->appToken = $this->adapterConfig->getValue("{$section}.authToken");
        $this->appTokenExpireAt = strtotime("+ 7200 seconds");

        $this->service = new Services\OAuthService($this->serviceConfig);
    }

    /**
     * Store and returns access token data information, cache and validate token validity,
     * require a new token if invalid or expired
     *
     * @return string
     */
    public function getAppToken()
    {
        /**
         * Validate if token exists and if is expired
         */
        if (!is_null($this->appToken) && ($this->appTokenExpireAt > time())) {
            return $this->appToken;
        }

        /**
         * Get token data
         */
        $response = $this->service->getAppToken();

        /**
         * Store access_token and expire time
         */
        $this->appToken = $response->access_token;
        $this->appTokenExpireAt = strtotime("+ {$response->expires_in} seconds");

        return $this->appToken;
    }

    /**
     * @return TradingService|null
     */
    protected function getTradingService()
    {
        if (!is_null($this->tradingService)) {
            return $this->tradingService;
        }

        $this->tradingService = new TradingService($this->serviceConfig);

        return $this->tradingService;
    }

    /**
     * Returns simple solds list
     *
     * @return array|bool
     */
    public function getSoldList()
    {
        $transactionsList = [];

        $service = new TradingService($this->serviceConfig);
        $request = new Types\GetMyeBaySellingRequestType();

        $request->RequesterCredentials = new Types\CustomSecurityHeaderType();
        $request->RequesterCredentials->eBayAuthToken = $this->getAppToken();

        /**
         * Request that eBay returns the list of actively selling items.
         * We want 10 items per page and they should be sorted in descending order by the current price.
         */
        $request->SoldList = new Types\ItemListCustomizationType();
        $request->SoldList->Include = true;
        $request->SoldList->Sort = Enums\ItemSortTypeCodeType::C_END_TIME;

        $response = $service->getMyeBaySelling($request);

        if (isset($response->Errors)) {
            foreach ($response->Errors as $error) {
                printf(
                    "%s: %s\n%s\n\n",
                    $error->SeverityCode === Enums\SeverityCodeType::C_ERROR ? 'Error' : 'Warning',
                    $error->ShortMessage,
                    $error->LongMessage
                );
            }

            return false;
        }

        if ($response->Ack !== 'Failure' && isset($response->SoldList)) {
            foreach ($response->SoldList->OrderTransactionArray->OrderTransaction as $transaction) {
                $trData = new Transaction();

                $trData->totalPrice = $transaction->Transaction->TotalPrice->value;
                $trData->currency = $transaction->Transaction->TotalPrice->currencyID;

                if ($transaction->Transaction->PaidTime instanceof \DateTime) {
                    $trData->paidTime = $transaction->Transaction->PaidTime->format('Y-m-d H:i:s');
                }

                $trData->paymentStatus = $transaction->Transaction->SellerPaidStatus;
                $trData->marketTransactionId = $transaction->Transaction->TransactionID;
                $trData->quantityPurchased = $transaction->Transaction->QuantityPurchased;
                $trData->purchasePrice = $transaction->Transaction->Item->BuyItNowPrice->value;

                $trData->customerData->customerName = $transaction->Transaction->Buyer->UserFirstName;
                $trData->customerData->customerSurame = $transaction->Transaction->Buyer->UserLastName;
                $trData->customerData->country = $transaction->Transaction->Buyer->BuyerInfo->ShippingAddress->Country;
                $trData->customerData->postalCode = $transaction->Transaction->Buyer->BuyerInfo->ShippingAddress->PostalCode;

                $trData->customerData->customerMail = $transaction->Transaction->Buyer->Email;
                $trData->customerData->userId = $transaction->Transaction->Buyer->UserID;

                $trData->productData->marketProductId = $transaction->Transaction->Item->ItemID;
                $trData->productData->vendorProductId = $transaction->Transaction->Item->SKU;
                $trData->productData->description = $transaction->Transaction->Item->Title;

                $transactionsList[] = $trData;
            }
        }

        return $transactionsList;
    }

    /**
     * Returns simple solds list
     *
     * @param null|DateTime $intervalStart
     * @param null|DateTime $intervalEnd
     * @return array|bool
     */
    public function getSoldListings($intervalStart = null, $intervalEnd = null)
    {
        $transactionsList = [];

        $request = new Types\GetSellingManagerSoldListingsRequestType();

        $request->RequesterCredentials = new Types\CustomSecurityHeaderType();
        $request->RequesterCredentials->eBayAuthToken = $this->getAppToken();

        if ($intervalStart instanceof \DateTime && $intervalEnd instanceof \DateTime) {
            $request->SaleDateRange = new Types\TimeRangeType();
            $request->SaleDateRange->TimeFrom = $intervalStart;
            $request->SaleDateRange->TimeTo = $intervalEnd;
        }

        $response = $this->getTradingService()->getSellingManagerSoldListings($request);

        if (isset($response->Errors)) {
            return false;
        }

        if ($response->Ack !== 'Failure' && isset($response->SaleRecord)) {
            foreach ($response->SaleRecord as $record) {
                foreach ($record->SellingManagerSoldTransaction as $transaction) {
                    $trData = $this->buildTransaction($transaction);

                    if ($record->OrderStatus->PaidTime instanceof \DateTime) {
                        $trData->paidTime = $record->OrderStatus->PaidTime->format('Y-m-d H:i:s');
                    }

                    $trData->purchasePrice = $record->OrderStatus->PaidStatus;
                    $trData->paymentStatus = $record->OrderStatus->PaidStatus;
                    $trData->paymentMethod = $record->OrderStatus->PaymentMethodUsed;

                    if ($record->CreationTime instanceof \DateTime) {
                        $trData->transactionDate = $record->CreationTime->format('Y-m-d H:i:s');
                    }

                    /**
                     * Parse customer data
                     */
                    $trData->customerData->userId = $record->BuyerID;
                    $trData->customerData->customerMail = $record->BuyerEmail;

                    $trData->totalPrice = $record->TotalAmount->value;
                    $trData->currency = $record->TotalAmount->currencyID;
                    $trData->purchasePrice = $record->SalePrice->value;

                    $trData->shippingData->status = $record->OrderStatus->ShippedStatus;
                    $trData->shippingData->cost = $record->ActualShippingCost->value;

                    if (count($response->SaleRecord) > 1 && $trData->shippingData->contact == "") {
                        $trData = $this->populateShippingData($transaction->OrderLineItemID, $trData);
                    }

                    $transactionsList[$record->SaleRecordID][] = $trData;
                }
            }
        }

        ksort($transactionsList);

        return $transactionsList;
    }

    /**
     * Parse a SellingManagerSoldOrderType and return a Transaction object
     *
     * @param \DTS\eBaySDK\Trading\Types\SellingManagerSoldTransactionType $transaction
     * @return Transaction
     */
    protected function buildTransaction($transaction)
    {
        $saleRecord = new Types\GetSellingManagerSaleRecordRequestType();
        $saleRecord->RequesterCredentials = new Types\CustomSecurityHeaderType();
        $saleRecord->RequesterCredentials->eBayAuthToken = $this->getAppToken();

        $trData = new Transaction();

        /**
         * Parse transaction data
         */
        $trData->marketTransactionId = $transaction->TransactionID;
        $trData->saleCounter = $transaction->SaleRecordID;
        $trData->quantityPurchased = $transaction->QuantitySold;

        /**
         * Parse product data
         */
        $trData->productData->marketProductId = $transaction->ItemID;
        $trData->productData->description = $transaction->ItemTitle;
        $trData->productData->vendorProductId = $transaction->CustomLabel;

        /**
         * Get shipping information
         */
        $saleRecord->ItemID = $transaction->ItemID;
        $saleRecord->TransactionID = (string)$transaction->TransactionID;
        $saleRecordData = $this->getTradingService()->getSellingManagerSaleRecord($saleRecord);

        /**
         * Parse shipping information
         */
        if (isset($saleRecordData->SellingManagerSoldOrder->ShippingAddress->Name)) {
            $trData->shippingData->contact = $saleRecordData->SellingManagerSoldOrder->ShippingAddress->Name;
            $trData->customerData->customerName = $saleRecordData->SellingManagerSoldOrder->ShippingAddress->Name;
        }

        if (isset($saleRecordData->SellingManagerSoldOrder->ShippingAddress->Street1)) {
            $trData->shippingData->address = $saleRecordData->SellingManagerSoldOrder->ShippingAddress->Street1;
        }

        if (isset($saleRecordData->SellingManagerSoldOrder->ShippingAddress->CityName)) {
            $trData->shippingData->cityName = $saleRecordData->SellingManagerSoldOrder->ShippingAddress->CityName;
        }

        if (isset($saleRecordData->SellingManagerSoldOrder->ShippingAddress->StateOrProvince)) {
            $trData->shippingData->stateOrProvince = $saleRecordData->SellingManagerSoldOrder->ShippingAddress->StateOrProvince;
        }

        if (isset($saleRecordData->SellingManagerSoldOrder->ShippingAddress->Country)) {
            $trData->shippingData->countryCode = $saleRecordData->SellingManagerSoldOrder->ShippingAddress->Country;
        }

        if (isset($saleRecordData->SellingManagerSoldOrder->ShippingAddress->Phone)) {
            $trData->shippingData->phone = $saleRecordData->SellingManagerSoldOrder->ShippingAddress->Phone;
        }

        if (isset($saleRecordData->SellingManagerSoldOrder->ShippingAddress->PostalCode)) {
            $trData->shippingData->postalCode = $saleRecordData->SellingManagerSoldOrder->ShippingAddress->PostalCode;
        }

        if (isset($saleRecordData->SellingManagerSoldOrder->ShippingAddress->PostalCode)) {
            $trData->customerData->postalCode = $saleRecordData->SellingManagerSoldOrder->ShippingAddress->PostalCode;
        }

        if (isset($saleRecordData->SellingManagerSoldOrder->ShippingAddress->Phone2)) {
            $trData->shippingData->phone2 = $saleRecordData->SellingManagerSoldOrder->ShippingAddress->Phone2;
        }

        return $trData;
    }

    /**
     * Require and returns a user data object for requested customer
     *
     * @param string $userId
     * @return Types\GetUserResponseType
     */
    protected function getCustomerDetail($userId)
    {
        $request = new Types\GetUserRequestType();
        $request->UserID = $userId;
        $request->RequesterCredentials->eBayAuthToken = $this->getAppToken();

        $customerData = $this->getTradingService()->getUser($request);

        return $customerData;
    }

    /**
     * Returns detailed data for specific order or range.
     *
     * @param string $orderListingId Order listing ID string.
     * @return Types\GetOrdersResponseType
     */
    protected function getOrders($orderListingId)
    {
        $request = new Types\GetOrdersRequestType();
        $request->RequesterCredentials = new Types\CustomSecurityHeaderType();
        $request->RequesterCredentials->eBayAuthToken = $this->getAppToken();

        $request->OrderIDArray = new Types\OrderIDArrayType();
        $request->OrderIDArray->OrderID[] = $orderListingId;

        $ordersData = $this->getTradingService()->getOrders($request);

        return $ordersData;
    }

    /**
     * Append shipping data from parent transaction to a transaction data object.
     *
     * @param string $orderId Order listing ID.
     * @param Transaction $trData Transaction object.
     * @return Transaction
     */
    protected function populateShippingData($orderId, Transaction $trData)
    {
        $saleRecordData = $this->getOrders($orderId);

        if (!isset($saleRecordData->OrderArray->Order[0])) {
            return $trData;
        }

        $shippingData = $saleRecordData->OrderArray->Order[0]->ShippingAddress;

        /**
         * Parse shipping information
         */
        if (isset($shippingData->Name)) {
            $trData->shippingData->contact = $shippingData->Name;
            $trData->customerData->customerName = $shippingData->Name;
        }

        if (isset($shippingData->Street1)) {
            $trData->shippingData->address = $shippingData->Street1;
        }

        if (isset($shippingData->CityName)) {
            $trData->shippingData->cityName = $shippingData->CityName;
        }

        if (isset($shippingData->StateOrProvince)) {
            $trData->shippingData->stateOrProvince = $shippingData->StateOrProvince;
        }

        if (isset($shippingData->Country)) {
            $trData->shippingData->countryCode = $shippingData->Country;
        }

        if (isset($shippingData->Phone)) {
            $trData->shippingData->phone = $shippingData->Phone;
        }

        if (isset($shippingData->PostalCode)) {
            $trData->shippingData->postalCode = $shippingData->PostalCode;
        }

        if (isset($shippingData->PostalCode)) {
            $trData->customerData->postalCode = $shippingData->PostalCode;
        }

        if (isset($shippingData->Phone2)) {
            $trData->shippingData->phone2 = $shippingData->Phone2;
        }

        return $trData;
    }

    /**
     * @param Transaction $trData
     * @param null|boolean $shippingStatus
     * @param null|string $feedbackMessage
     * @return Types\CompleteSaleResponseType
     */
    public function completeSale(Transaction $trData, $shippingStatus = null, $feedbackMessage = null)
    {
        $request = new Types\CompleteSaleRequestType();
        $request->RequesterCredentials = new Types\CustomSecurityHeaderType();
        $request->RequesterCredentials->eBayAuthToken = $this->getAppToken();

        $request->TransactionID = (string)$trData->marketTransactionId;
        $request->ItemID = (string)$trData->productData->marketProductId;

        if (!is_null($shippingStatus)) {
            $request->Shipped = $shippingStatus;
        }

        if (!is_null($feedbackMessage)) {
            $request->FeedbackInfo = $feedbackMessage;
        }

        return $this->getTradingService()->completeSale($request);
    }
}
