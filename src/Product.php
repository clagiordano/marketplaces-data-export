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

    /**
     * @param string $name
     */
    function __get($name)
    {
        if (isset($this->{$name})) {
            return $this->{$name};
        }
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    function __set($name, $value)
    {
        $this->{$name} = $value;
    }


}