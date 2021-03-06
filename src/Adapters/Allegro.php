<?php

namespace clagiordano\MarketplacesDataExport\Adapters;

use clagiordano\MarketplacesDataExport\Config;
use clagiordano\MarketplacesDataExport\Product;
use clagiordano\MarketplacesDataExport\Transaction;
use DateTime;

/**
 * Class Allegro
 * @package clagiordano\MarketplacesDataExport\Adapters
 */
class Allegro extends AbstractAdapter
{
    /** @var string $resourceLink */
    protected $resourceLink = null;
    /** @var array $userSession */
    protected $userSession = null;
    /** @var array $apiInfo */
    protected $apiInfo = null;

    /**
     * Allegro constructor.
     *
     * @param Config $config
     * @param boolean $sandboxMode resource api link
     */
    public function __construct(Config $config, $sandboxMode = true)
    {
        parent::__construct($config, $sandboxMode);
    }

    /**
     * @inheritDoc
     */
    public function getAppToken()
    {
        // TODO: Implement getAppToken() method.
    }

    /**
     * @inheritDoc
     */
    public function getSellingTransactions($intervalStart = null, $intervalEnd = null)
    {
        // TODO: Implement getSellingTransactions() method.
    }

    /**
     * @param string $resourceLink
     */
    public function setResourceLink($resourceLink)
    {
        $this->resourceLink = $resourceLink;
    }

    /**
     * @return string
     */
    public function getResourceLink()
    {
        return $this->resourceLink;
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        if (isset($arguments[0])) $arguments = (array)$arguments[0];
        else $arguments = [];


        $arguments['sessionId'] = $this->userSession['sessionHandlePart'];
        $arguments['sessionHandle'] = $this->userSession['sessionHandlePart'];
        $arguments['webapiKey'] = $this->adapterConfig->apiKey;
//        $arguments['countryId'] = $this->countryId;
        $arguments['countryCode'] = $this->adapterConfig->countryCode;
        return $this->soapClient->$name($arguments);
    }

    /**
     * Returns allegro system information
     *
     * @return array
     */
    public function getApiInfo()
    {
        if (!is_null($this->apiInfo)) {
            return $this->apiInfo;
        }

        $infoData = $this->getSoapClient($this->resourceLink, true)
            ->call(
                'doQueryAllSysStatus',
                [
                    [
                        'countryId' => $this->adapterConfig->countryCode,
                        'webapiKey' => $this->adapterConfig->apiKey
                    ]
                ]
            );

        if (isset($infoData['sysCountryStatus']['item'])) {
            foreach ($infoData['sysCountryStatus']['item'] as $info) {
                if ($info['countryId'] == $this->adapterConfig->countryCode) {
                    return $info;
                }
            }
        }

        throw new \InvalidArgumentException(
            __METHOD__ . ": Failed to get system information, please check configuration"

        );
    }

    /**
     * Perform login operation, store and return user session
     *
     * @return array
     */
    public function doLogin()
    {
        if (!is_null($this->userSession)) {
            return $this->userSession;
        }

        $this->userSession = $this->getSoapClient($this->resourceLink, true)
            ->call(
                'doLoginEnc',
                [
                    'parameters' => [
                        'userLogin' => $this->adapterConfig->userLogin,
                        'userHashPassword' => base64_encode(hash('sha256', $this->adapterConfig->userPassword, true)),
                        'countryCode' => $this->adapterConfig->countryCode,
                        'webapiKey' => $this->adapterConfig->apiKey,
                        'localVersion' => (int)$this->getApiInfo()['verKey']
                    ]
                ]
            );

        if (!isset($this->userSession['sessionHandlePart'])) {
            throw new \InvalidArgumentException(
                __METHOD__ . ": Failed to logIn, please check configuration"

            );
        }

        return $this->userSession;
    }

    /**
     * This method provides all functions of “Selling” tabs available in My Allegro.
     * Additionally it allows for sorting and filtering offers and searching by name.
     *
     * @return array
     */
    public function getMySellItems()
    {
        return $this->getSoapClient($this->resourceLink, true)
            ->call(
                'doGetMySellItems',
                [
                    'sessionId' => $this->doLogin()['sessionHandlePart']
                ]
            );
    }

    /**
     * This method provides all functions of ”Sold” tabs available in My Allegro.
     * Additionally it allows for sorting and filtering offers and searching by name.
     *
     * @return array
     */
    public function getMySoldItems()
    {
        return $this->getSoapClient($this->resourceLink, true)
            ->call(
                'doGetMySoldItems',
                [
                    'sessionId' => $this->doLogin()['sessionHandlePart']
                ]
            );
    }

    /**
     *﻿This method allows for loading publicly available information on any user.
     * The user can be indicated by the identifier or username - when value is passed in both parameters,
     * data of a user indicated by the userId parameter are returned
     *
     * @param int $userId required (non-required if userLogin has been provided)
     * @param null $userLogin required (non-required if userId has been provided)
     * @return array
     */
    public function getUserInfo($userId = null, $userLogin = null)
    {
        if (is_null($userId) && is_null($userLogin)) {
            throw new \InvalidArgumentException(
                __METHOD__ . "Error, userId or userName are required!"
            );
        }

        $params = [
            'webapiKey' => $this->adapterConfig->apiKey,
            'countryId' => $this->adapterConfig->countryCode,
        ];

        if (!is_null($userId)) {
            $params['userId'] = $userId;
        }

        if (!is_null($userLogin)) {
            $params['userLogin'] = $userLogin;
        }

        return $this->getSoapClient($this->resourceLink, true)
            ->call(
                'doShowUser',
                $params
            );
    }

    /**
     *﻿This method allows for loading single purchase events concluded by a given buyer in an indicated offer
     * (in which a logged-in user acts as the seller). That method returns only purchase events which is not paid yet
     * while calling the method. The exception is a situation when a purchase had been paid for but the payment
     * has been cancelled - that purchase is treated as unpaid and information about it will be returned.
     * In case of providing an incorrect user identifier an empty structure is returned.
     *
     * @param int $itemId
     * @param int $buyerId
     * @return array
     */
    public function getDeals($itemId, $buyerId)
    {
        return $this->getSoapClient($this->resourceLink, true)
            ->call(
                'doGetDeals',
                [
                    'sessionId' => $this->doLogin()['sessionHandlePart'],
                    'itemId' => $itemId,
                    'buyerId' => $buyerId
                ]
            );
    }

    /**
     * ﻿This method allows for loading full contact data of trading partners from the given offer.
     * It returns various data - depends on whether a logged user acts as a seller (userData, userSentToData)
     * or a buyer (userData, userBankAccounts, companySecondAddress) in an offer. In case of providing an incorrect
     * offer identifier an empty structure is returned. The method does not return data for offers moved
     * to the archive.
     *
     * @param array $itemsArray
     * @return array
     */
    public function getBuyerData($itemsArray)
    {
        return $this->getSoapClient($this->resourceLink, true)
            ->call(
                'doGetPostBuyData',
                [
                    'sessionHandle' => $this->doLogin()['sessionHandlePart'],
                    'itemsArray' => $itemsArray
                ]
            );
    }

    /**
     * This method allows for loading information from the event log on events (creating purchase event,
     * creating transaction, cancelling transaction, completing transaction) related to after-sale forms
     * in a context of a logged-in user being the transaction's party (data are returned only for offers
     * created by the user in a country to which he/she is logged-in to while calling the method).
     * 100 of most recent events are returned (starting from the point set in the journalStart parameter)
     * sorted in ascending order by the time of their appearance. In order to control the process of loading
     * next data portions (to get to the most recent data) the journalStart parameter has to pass the
     * dealEventId value of the last (hundredth) element returned when calling the method and you need to
     * repeat the process until you receive a data portion containing less than 100 elements (that means the
     * received data are up-to-date).
     *
     * @param null $journalStart
     * @return mixed
     */
    public function getJournalDeals($journalStart = null)
    {
        $params = [
            'sessionId' => $this->doLogin()['sessionHandlePart'],
        ];

        if (!is_null($journalStart)) {
            $params['journalStart'] = $journalStart;
        }

        return $this->getSoapClient($this->resourceLink, true)
            ->call(
                'doGetSiteJournalDeals',
                $params
            );
    }

    /**
     * This method allows sellers to load data from after-sale and related additional payment forms
     * filled out by buyers. It also returns detailed payment data (made through PayU) related to the
     * indicated transactions, information on a selected pick-up point and identification data on
     * shipment containing products from particular transactions. If incorrect transaction identifiers
     * or the ones which cannot be accessed by a logged-in user are provided in the input array, they
     * are ignored when presenting output data (data are returned only for transaction identifiers
     * considered correct and relating to the user being a session owner). Additionally - calling this
     * method for a transaction in which a logged-in user has acted as a buyer will result in returning
     * an empty structure. The doGetPostBuyFormsDataForBuyers method should be used for such purpose.
     *
     * @param array $transactionsIds
     * @return mixed
     */
    public function getTransactionsData($transactionsIds)
    {
        return $this->getSoapClient($this->resourceLink, true)
            ->call(
                'doGetPostBuyFormsDataForSellers',
                [
                    'sessionId' => $this->doLogin()['sessionHandlePart'],
                    'transactionsIdsArray' => $transactionsIds
                ]
            );
    }

    /**
     * This method allows for loading values of transaction identifiers (purchases completed by
     * filling out an after-sale form by a buyer) and related additional payments based on passed
     * offer identifiers. Results can be filtered by delivery methods by providing their identifiers
     * while calling. Results are sorted in the same order in which forms related to the given offer
     * have been filled out (since the most recent to the oldest one). Received transaction identifiers
     * can be used e.g. to load filled out after-sale forms using the doGetPostBuyFormsDataForSellers/ForBuyers
     * method. This method returns only transaction identifiers with filled out by a buyer after-sale
     * forms (within the given offer).
     *
     * @param array $itemIds
     * @param string $userRole seller or buyer
     * @return mixed
     */
    public function getTransactionsIds(array $itemIds, $userRole = 'buyer')
    {
        return $this->getSoapClient($this->resourceLink, true)
            ->call(
                'doGetPostBuyFormsDataForSellers',
                [
                    'sessionId' => $this->doLogin()['sessionHandlePart'],
                    'itemsIdArray' => $itemIds,
                    'userRole' => $userRole,
//                    'shipmentIdArray' => array(2, 5)
                ]
            );
    }

    /**
     * @inheritDoc
     */
    public function completeSale(Transaction $trData, $shippingStatus = null, $feedbackMessage = null)
    {
        // TODO: Implement completeSale() method.
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
