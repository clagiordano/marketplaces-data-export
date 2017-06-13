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
    /** @var string|null $description */
    public $description = null;
    /** @var int $storedAmount */
    public $storedAmount = 0;

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