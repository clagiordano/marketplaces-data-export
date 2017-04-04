<?php

namespace clagiordano\MarketplacesDataExport\Adapters;

use clagiordano\MarketplacesDataExport\Config;
use \DTS\eBaySDK\OAuth\Services;
use \DTS\eBaySDK\OAuth\Types;

/**
 * Class Ebay
 * @package clagiordano\MarketplacesDataExport\Adapters
 */
class Ebay extends AbstractAdapter
{
    protected $service;

    public function __construct(Config $config)
    {
        $this->service = new Services\OAuthService([
            'credentials' => $config['sandbox']['credentials'],
            'ruName'      => $config['sandbox']['ruName'],
            'sandbox'     => true
        ]);
    }
}
