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
        return (static::$storesLookup[$storeCode]);
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
