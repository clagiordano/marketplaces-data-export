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
//        $request->SoldList->Pagination = new Types\PaginationType();
//        $request->SoldList->Pagination->EntriesPerPage = 10;
        $request->SoldList->Sort = Enums\ItemSortTypeCodeType::C_END_TIME;
        $request->SoldList->Sort = Enums\ItemSortTypeCodeType::C_CURRENT_PRICE_DESCENDING;

        $response = $service->getMyeBaySelling($request);
//        print_r($response);

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
//                print_r($transaction);
//                die("AAA");
//                printf(
//                    "[%s]: (%s) %s: %s %s %s \n",
//                    $transaction->Transaction->Item->SKU,
//                    $transaction->Transaction->Item->ItemID,
//                    $transaction->Transaction->Item->Title,
//                    $transaction->Transaction->Item->Currency,
//                    $transaction->Transaction->Item->BuyItNowPrice->currencyID,
//                    $transaction->Transaction->Item->BuyItNowPrice->value
//                );

                $trData = new Transaction();

                $trData->customerData->customerMail = $transaction->Transaction->Buyer->Email;
                $trData->customerData->userId = $transaction->Transaction->Buyer->UserID;
                $trData->customerData->customerName = $transaction->Transaction->Buyer->UserFirstName;
                $trData->customerData->customerSurame = $transaction->Transaction->Buyer->UserLastName;
                $trData->shippingAddress = $transaction->Transaction->Buyer->ShippingAddress;

                $transactionsList[] = $trData;

//                return $transactionsList;
            }
        }

        return $transactionsList;

//        $pageNum = 1;
//        do {
//            $request->SoldList->Pagination->PageNumber = $pageNum;
//            /**
//             * Send the request.
//             */
//            $response = $service->getMyeBaySelling($request);
//            /**
//             * Output the result of calling the service operation.
//             */
//            echo "==================\nResults for page $pageNum\n==================\n";
//            if (isset($response->Errors)) {
//                foreach ($response->Errors as $error) {
//                    printf(
//                        "%s: %s\n%s\n\n",
//                        $error->SeverityCode === Enums\SeverityCodeType::C_ERROR ? 'Error' : 'Warning',
//                        $error->ShortMessage,
//                        $error->LongMessage
//                    );
//                }
//            }
//
////            print_r($response->SoldList->OrderTransactionArray->OrderTransaction);
//
//            if ($response->Ack !== 'Failure' && isset($response->SoldList)) {
//                foreach ($response->SoldList->OrderTransactionArray->OrderTransaction as $transaction) {
//                    print_r($transaction->Transaction->Item);
//                    die("AAA");
//                    printf(
//                        "[%s]: (%s) %s: %s %s %s \n",
//                        $transaction->Transaction->Item->SKU,
//                        $transaction->Transaction->Item->ItemID,
//                        $transaction->Transaction->Item->Title,
//                        $transaction->Transaction->Item->Currency,
//                        $transaction->Transaction->Item->BuyItNowPrice->currencyID,
//                        $transaction->Transaction->Item->BuyItNowPrice->value
//                    );
//
//                }
//            }
//            $pageNum += 1;
//        } while (isset($response->SoldList) && $pageNum <= $response->SoldList->PaginationResult->TotalNumberOfPages);
    }
}
