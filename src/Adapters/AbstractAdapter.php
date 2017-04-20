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

    /**
     * AbstractAdapter constructor.
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->adapterConfig = $config;
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
