<?php

class PassportHelper
{
    public static function getStoreString()
    {
        $returnval = false;
        $storeName = false;

        if (Session::has('storeContext')) {
            $storeNumber = Session::get('storeContext');
            if($storeNumber == '000') {
                $storeName = 'Corporate';
            } else {
                $storeName = StoresLookup::where('code', Session::get('storeContext'))->first()->store_name;
            }

            if ($storeName) {
                $returnval = $storeNumber . ' - ' . $storeName;
            }
        }

        return $returnval;
    }

    public static function getStoreNumber()
    {
        $returnval = false;

        if (Session::has('storeContext')) {
            $storeNumber = Session::get('storeContext');
            $returnval = $storeNumber;
        }

        return $returnval;
    }
}
