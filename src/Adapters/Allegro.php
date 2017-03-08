<?php

namespace clagiordano\MarketplacesDataExport\Adapters;

use clagiordano\MarketplacesDataExport\Config;

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
     * @param Config $config
     * @param string $resourceLink resource api link
     */
    public function __construct(Config $config, $resourceLink)
    {
        parent::__construct($config);

        $this->resourceLink = $resourceLink;
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
    public function getBuyerData(array $itemsArray)
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


}
