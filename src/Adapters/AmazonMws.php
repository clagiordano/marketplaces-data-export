<?php
/**
 * Created by PhpStorm.
 * User: claudio
 * Date: 30/01/18
 * Time: 15.10
 */

namespace clagiordano\MarketplacesDataExport\Adapters;

use clagiordano\MarketplacesDataExport\Config;

/**
 * Class AmazonMws
 * @package clagiordano\MarketplacesDataExport\Adapters
 */
class AmazonMws extends AbstractAdapter
{
    /**
     * Ebay constructor.
     * @param Config $config
     * @param bool $sandboxMode
     */
    public function __construct(Config $config, $sandboxMode = true)
    {
        parent::__construct($config, $sandboxMode);

        $section = 'sandbox';
        if (!$this->isSandboxMode) {
            $section = 'production';
        }

        $this->serviceConfig = [];
        $this->appToken = $this->adapterConfig->getValue("{$section}.authToken");
        $this->appTokenExpireAt = strtotime("+ 7200 seconds");
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

}
