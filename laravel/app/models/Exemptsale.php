<?php

class Exemptsale extends Eloquent
{
    protected $table = 'exemptsales';

    public static function getSubmissionHistoryForCurrentStore($days = 30)
    {

        $dt = new DateTime('today');
        $dt->sub(new DateInterval('P' . $days . 'D'));

        return Exemptsale::where('store_id', '=', PassportHelper::getStoreNumber())->where('created_at', '>=', $dt)->orderBy('created_at', 'desc')->get();
    }
}
