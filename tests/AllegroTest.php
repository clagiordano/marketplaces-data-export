<?php

namespace clagiordano\MarketplacesDataExport\Tests;

use clagiordano\MarketplacesDataExport\Adapters\Allegro;
use clagiordano\MarketplacesDataExport\Config;

/**
 * Class AllegroTest
 * @package clagiordano\MarketplacesDataExport\Tests
 */
class AllegroTest extends \PHPUnit_Framework_TestCase
{
    /** @var Allegro $adapter */
    protected $adapter = null;

    public function setUp()
    {
        $config = new Config(__DIR__ . '/../testdata/allegro.php');
        $this->assertInstanceOf('clagiordano\MarketplacesDataExport\Config', $config);

        $this->adapter = new Allegro($config, 'https://webapi.allegro.pl/service.php?wsdl');
        $this->assertInstanceOf(
            'clagiordano\MarketplacesDataExport\Adapters\Allegro',
            $this->adapter
        );
    }

    public function testGetSoapClient()
    {
//        var_dump($this->adapter->getSoapClient('https://webapi.allegro.pl/service.php?wsdl', true));
    }

    public function testGetSystemStatus()
    {
//        var_dump($this->adapter->getSystemStatus());
    }
}