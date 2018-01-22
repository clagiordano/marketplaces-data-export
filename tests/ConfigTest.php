<?php

namespace clagiordano\MarketplacesDataExport\Tests;

use clagiordano\MarketplacesDataExport\Adapters\Allegro;
use clagiordano\MarketplacesDataExport\Config;

/**
 * Class ConfigTest
 * @package clagiordano\MarketplacesDataExport\Tests
 */
class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /** @var Config $class */
    protected $class = null;

    protected function setUp()
    {
        $testConfig = __DIR__ . '/../testdata/ebay.php';
        $this->assertFileExists($testConfig);
        $this->class = new Config($testConfig);
        $this->assertInstanceOf('clagiordano\MarketplacesDataExport\Config', $this->class);
    }

    /**
     * @test
     */
    public function canGetCredentialsFromConfig()
    {
        $section = "production";
        $this->assertNotNull(
            $this->class->getValue("{$section}.credentials")
        );
    }

    /**
     * @test
     */
    public function canGetAuthTokenFromConfig()
    {
        $section = "production";
        $this->assertNotNull(
            $this->class->getValue("{$section}.authToken")
        );
    }
}
