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

        $this->serviceConfig = [
            'Marketplace_Id' => $this->adapterConfig->getValue("{$section}.Marketplace_Id"),
            'Seller_Id' => $this->adapterConfig->getValue("{$section}.Seller_Id"),
            'Access_Key_ID' => $this->adapterConfig->getValue("{$section}.Access_Key_ID"),
            'Secret_Access_Key' => $this->adapterConfig->getValue("{$section}.Secret_Access_Key"),
            'MWSAuthToken' => $this->adapterConfig->getValue("{$section}.MWSAuthToken"),
        ];
    }

    /**
     * @inheritDoc
     */
    public function getAppToken()
    {
        /**
         * Validate if token is set
         */
        if (!is_null($this->appToken)) {
            return $this->appToken;
        }

        $this->appToken = $this->serviceConfig['MWSAuthToken'];
        $this->appTokenExpireAt = strtotime("+ 7200 seconds");

        return $this->appToken;
    }

    /**
     * @inheritDoc
     */
    public function getSellingTransactions($intervalStart = null, $intervalEnd = null)
    {
        // TODO: Implement getSellingTransactions() method.
    }

}
