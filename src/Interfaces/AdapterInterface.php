<?php

namespace clagiordano\MarketplacesDataExport\Interfaces;

use clagiordano\MarketplacesDataExport\Config;
use DateTime;

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

    /**
     * Returns a list of selling transactions between datetime interval range,
     * if no interval is provided, returns all possible transactions.
     *
     * @param null|DateTime $intervalStart
     * @param null|DateTime $intervalEnd
     * @return array
     * @throws \RuntimeException on request failure
     */
    public function getSellingTransactions($intervalStart = null, $intervalEnd = null);
}
