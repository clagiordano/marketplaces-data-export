<?php

namespace spec\clagiordano\MarketplacesDataExport\Adapters;

use clagiordano\MarketplacesDataExport\Adapters\Ebay;
use clagiordano\MarketplacesDataExport\Config;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * Class EbaySpec
 * @package spec\clagiordano\MarketplacesDataExport\Adapters
 */
class EbaySpec extends ObjectBehavior
{
    function it_is_initializable(Config $config)
    {
        $config->loadConfig('../../../../testdata/ebay.php');
        $this->beConstructedWith($config);
        $this->shouldHaveType(Ebay::class);
    }
}
