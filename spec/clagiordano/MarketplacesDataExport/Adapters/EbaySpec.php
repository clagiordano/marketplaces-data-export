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
    protected $configPath = __DIR__ . '/../../../../testdata/ebay.php';

    /**
     */
    function it_is_initializable()
    {
        $config = new Config($this->configPath);
        $this->beConstructedWith($config, false);
        $this->shouldHaveType(Ebay::class);
    }

    /**
     */
    function it_returns_app_token()
    {
        $config = new Config($this->configPath);
        $this->beConstructedWith($config, false);
        $this->getAppToken()->shouldMatch('/\w+/');
    }
}
