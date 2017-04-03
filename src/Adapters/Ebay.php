<?php

namespace clagiordano\MarketplacesDataExport\Adapters;

use clagiordano\MarketplacesDataExport\Config;
use \DTS\eBaySDK\Shopping\Services;
use \DTS\eBaySDK\Shopping\Types;


/**
 * Class Ebay
 * @package clagiordano\MarketplacesDataExport\Adapters
 */
class Ebay extends AbstractAdapter
{
    /** @var null|Services\ShoppingService $service */
    protected $service = null;

    protected $request = null;

    /**
     * Ebay constructor.
     * @param Config $config
     * @param string $resourceLink resource api link
     */
    public function __construct(Config $config, $resourceLink)
    {
        parent::__construct($config);

        $this->resourceLink = $resourceLink;
    }
}
