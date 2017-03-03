<?php

namespace clagiordano\MarketplacesDataExport;

use clagiordano\weblibs\configmanager\ConfigManager;

/**
 * Class Config
 * @package clagiordano\MarketplacesDataExport
 */
class Config
{
    /** @var ConfigManager|null $configManager */
    protected $configManager = null;

    /** @var string|null $userLogin */
    public $userLogin = null;
    /** @var string|null $userPassword */
    public $userPassword = null;
    /** @var int|null $countryCode */
    public $countryCode = null;
    /** @var string|null $apiKey */
    public $apiKey = null;
    /** @var int|null $apiVersion */
    public $apiVersion = null;

    /**
     * Config constructor.
     * @param string $configFile
     */
    public function __construct($configFile)
    {
        $this->configManager = new ConfigManager($configFile);

        $this->setupProperties();
    }

    /**
     * Sets internal properties from config files
     */
    private function setupProperties()
    {
        $this->userLogin = $this->configManager->getValue('userLogin', null);
        $this->userPassword = $this->configManager->getValue('userPassword', null);
        $this->countryCode = $this->configManager->getValue('countryCode', null);
        $this->apiKey = $this->configManager->getValue('apiKey', null);
        $this->apiVersion = $this->configManager->getValue('apiVersion', null);
    }
}
