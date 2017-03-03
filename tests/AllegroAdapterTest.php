<?php

namespace clagiordano\MarketplacesDataExport\Tests;

use clagiordano\MarketplacesDataExport\Adapters\Allegro;
use clagiordano\MarketplacesDataExport\Config;

/**
 * Class AllegroAdapterTest
 * @package clagiordano\MarketplacesDataExport\Tests
 */
class AllegroAdapterTest extends \PHPUnit_Framework_TestCase
{
    /** @var Allegro $adapter */
    protected $adapter = null;

    public function setUp()
    {
        $config = new Config(__DIR__ . '/../testdata/allegro.php');
        var_dump($config);

        $this->adapter = new Allegro($config);
        $this->assertInstanceOf(
            'clagiordano\MarketplacesDataExport\Adapters\AllegroAdapter',
            $this->adapter
        );
    }

    public function testBasic()
    {

    }
}