<?php

namespace clagiordano\MarketplacesDataExport;

use clagiordano\weblibs\configmanager\ConfigManager;

/**
 * Class Config
 * @package clagiordano\MarketplacesDataExport
 */
class Config extends ConfigManager
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
        parent::__construct($configFile);

        $this->setupProperties();
    }

    /**
     * Sets internal properties from config files
     */
    private function setupProperties()
    {
        $this->userLogin = $this->getValue('userLogin', null);
        $this->userPassword = $this->getValue('userPassword', null);
        $this->countryCode = $this->getValue('countryCode', null);
        $this->apiKey = $this->getValue('apiKey', null);
        $this->apiVersion = $this->getValue('apiVersion', null);
    }
}
