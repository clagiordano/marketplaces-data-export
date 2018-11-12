<?php

namespace clagiordano\MarketplacesDataExport\Interfaces;

use clagiordano\MarketplacesDataExport\Config;
use clagiordano\MarketplacesDataExport\Product;
use clagiordano\MarketplacesDataExport\Transaction;
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

    /**
     * Marks a transaction shipped or not
     *
     * @param Transaction $trData
     * @return mixed
     */
    public function completeSale(Transaction $trData);

    /**
     * Returns a product array of available marketplace items or a request number to fetch
     *
     * @return Product[]|int
     */
    public function getSellingList();

    /**
     * Updates stock amount available for one or more products.
     *
     * @param Product[] $products Supported up to MAX_REVISE_AT_TIME products at time.
     * @return boolean Operation status;
     */
    public function updateSellingProducts(array $products);
}
