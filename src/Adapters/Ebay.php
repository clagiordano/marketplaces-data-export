<?php

namespace clagiordano\MarketplacesDataExport\Adapters;

use clagiordano\MarketplacesDataExport\Config;
use clagiordano\MarketplacesDataExport\Transaction;
use \DTS\eBaySDK\OAuth\Services;
use \DTS\eBaySDK\Constants\SiteIds;
use DTS\eBaySDK\Trading\Services\TradingService;
use \DTS\eBaySDK\Trading\Types;
use \DTS\eBaySDK\Trading\Enums;

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
     * @return array|bool
     */
    public function getSoldListings()
    {
        $transactionsList = [];

        $this->tradingService = new TradingService($this->serviceConfig);
        $request = new Types\GetSellingManagerSoldListingsRequestType();


        $request->RequesterCredentials = new Types\CustomSecurityHeaderType();
        $request->RequesterCredentials->eBayAuthToken = $this->getAppToken();


        $response = $this->tradingService->getSellingManagerSoldListings($request);

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

                    $transactionsList[$record->SaleRecordID][] = $trData;
                }

                // TODO: Remove
//                return $transactionsList;
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

        /** TODO Check if array has only 1 element */

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
        $saleRecordData = $this->tradingService->getSellingManagerSaleRecord($saleRecord);

        /**
         * Parse shipping information
         */
        $trData->shippingData->contact = $saleRecordData->SellingManagerSoldOrder->ShippingAddress->Name;
        $trData->customerData->customerName = $saleRecordData->SellingManagerSoldOrder->ShippingAddress->Name;
        $trData->shippingData->address = $saleRecordData->SellingManagerSoldOrder->ShippingAddress->Street1;
        $trData->shippingData->cityName = $saleRecordData->SellingManagerSoldOrder->ShippingAddress->CityName;
        $trData->shippingData->stateOrProvince = $saleRecordData->SellingManagerSoldOrder->ShippingAddress->StateOrProvince;
        $trData->shippingData->countryCode = $saleRecordData->SellingManagerSoldOrder->ShippingAddress->Country;
        $trData->shippingData->phone = $saleRecordData->SellingManagerSoldOrder->ShippingAddress->Phone;
        $trData->shippingData->postalCode = $saleRecordData->SellingManagerSoldOrder->ShippingAddress->PostalCode;

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

        $customerData = $this->tradingService->getUser($request);

        return $customerData;
    }
}
