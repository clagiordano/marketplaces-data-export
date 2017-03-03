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
     * Returns allegro system informations
     *
     * @return array
     */
    public function getApiInfo()
    {
        if (!is_null($this->apiInfo)) {
            return $this->apiInfo;
        }

        $client = $this->getSoapClient($this->resourceLink, true);
        $infoData = $client->call(
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

    public function doLogin()
    {
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

        return $this;
    }

    public function getTest()
    {

    }
}
