<?php

namespace clagiordano\MarketplacesDataExport;

/**
 * Class Product
 * @package clagiordano\MarketplacesDataExport
 */
class Product
{
    /** @var null|int $marketProductId */
    public $marketProductId = null;
    /** @var string|null $vendorProductId */
    public $vendorProductId = null;
    /** @var string $description */
    public $description = "";
    /** @var integer $storedAmount */
    public $storedAmount = 0;
    /** @var integer $availableAmount */
    public $availableAmount = 0;
    /** @var bool $isVariation */
    public $isVariation = false;

    /**
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        if (isset($this->{$name})) {
            return $this->{$name};
        }

        return null;
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        $this->{$name} = $value;
    }
}
