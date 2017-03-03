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

    public function getSystemStatus()
    {
        return $this->getSoapClient($this->resourceLink, true)
            ->call(
                'doQueryAllSysStatus',
                [
                    [
                        'countryId' => $this->adapterConfig->countryCode,
                        'webapiKey' => $this->adapterConfig->apiKey
                    ]
                ]
            );
    }

    public function getTest()
    {

    }
}
