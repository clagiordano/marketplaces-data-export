<?php

namespace clagiordano\MarketplacesDataExport\Adapters;

use clagiordano\MarketplacesDataExport\Config;
use clagiordano\MarketplacesDataExport\Interfaces\AdapterInterface;

/**
 * Class AbstractAdapter
 * @package clagiordano\MarketplacesDataExport\Adapters
 */
abstract class AbstractAdapter implements AdapterInterface
{
    /** @var Config $adapterConfig */
    protected $adapterConfig = null;
    /** @var \nusoap_client $soapClient */
    protected $soapClient = null;
    /** @var bool $isSandboxMode */
    protected $isSandboxMode = true;
    /** @var array $serviceConfig */
    protected $serviceConfig = [];
    /** @var string $appToken */
    protected $appToken = null;
    /** @var int $appTokenExpireAt */
    protected $appTokenExpireAt = 0;

    /**
     * AbstractAdapter constructor.
     * @param Config $config
     * @param bool $sandboxMode
     */
    public function __construct(Config $config, $sandboxMode = true)
    {
        $this->adapterConfig = $config;
        $this->isSandboxMode = $sandboxMode;
    }

    /**
     * Returns a valid soap client for the specified resource
     *
     * @param string $resourceLink
     * @param bool $isWsdl
     * @return \nusoap_client
     */
    protected function getSoapClient($resourceLink, $isWsdl = false)
    {
        if (is_null($this->soapClient)) {
            $this->soapClient = new \nusoap_client($resourceLink, $isWsdl);
        }

        return $this->soapClient;
    }

    protected function doCall($functionName, array $functionArgs)
    {

    }
}
