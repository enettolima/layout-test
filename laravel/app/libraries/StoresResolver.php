<?php

class StoresResolver
{
    static $foo;

    static $storesLookup;

    public static function getInstance()
    {
        static $instance = null;

        if (null === $instance) {
            $instance = new static();
            self::init();
        } 

        return $instance;
    }

    public function getStore($storeCode)
    {
        if (array_key_exists($storeCode, static::$storesLookup)) {
            $returnval = static::$storesLookup[$storeCode];
        } else {
            $returnval = new stdclass();
            if ($storeCode === "000") {
                $returnval->store_name = "Corporate";
            } else {
                $returnval->store_name = "Not Found (?)";
            }
        }

        return $returnval;
    }

    protected static function init()
    {
        foreach (StoresLookup::all() as $store) {
            static::$storesLookup[$store->code] = $store;
        }
    }

    protected static function bar()
    {
        echo "I ran bar";
    }

    protected function __construct()
    {
    }

    private function __clone()
    {
    }

    private function __wakeup()
    {
    }
}
