<?php

namespace clagiordano\MarketplacesDataExport\Interfaces;

use clagiordano\MarketplacesDataExport\Config;

/**
 * Interface ExportInterface
 * @package clagiordano\MarketplacesDataExport\Interfaces
 */
interface AdapterInterface
{
    /**
     * AdapterInterface constructor.
     *
     * @param Config $config
     * @param bool $sandboxMode
     */
    public function __construct(Config $config, $sandboxMode = true);

    /**
     * Store and returns access token data information, cache and validate token validity,
     * require a new token if invalid or expired
     *
     * @return string
     */
    public function getAppToken();
}
