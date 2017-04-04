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
    protected $service = null;
    protected $appToken = null;
    protected $appTokenExpireAt = null;

    public function __construct(Config $config, $sandboxMode = true)
    {
        $section = 'sandbox';
        if (!$sandboxMode) {
            $section = 'production';
        }

        $this->service = new Services\OAuthService([
            'credentials' => $config->getValue("{$section}.credentials"),
            'ruName'      => $config->getValue("{$section}.ruName"),
            'sandbox'     => $sandboxMode
        ]);
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
}
