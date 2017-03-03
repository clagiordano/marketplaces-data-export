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
        $configFile = __DIR__ . '/../testdata/allegro.php';
        $this->assertFileExists($configFile);

        $config = new Config($configFile);
        $this->assertInstanceOf('clagiordano\MarketplacesDataExport\Config', $config);

        $this->adapter = new Allegro($config, 'https://webapi.allegro.pl/service.php?wsdl');
        $this->assertInstanceOf(
            'clagiordano\MarketplacesDataExport\Adapters\Allegro',
            $this->adapter
        );
    }

    /**
     * @group internal
     * @group info
     */
    public function testGetApiInfo()
    {
        $this->assertInternalType('array', $this->adapter->getApiInfo());
        $this->assertNotNull($this->adapter->getApiInfo()['verKey']);
    }

    /**
     * @group internal
     * @group login
     */
    public function testDoLogin()
    {
        $this->adapter->doLogin();
    }

    /**
     * @group public
     * @group sells
     */
    public function testGetMySells()
    {
        $sells = $this->adapter->getMySells();

        print_r($sells);
    }
}