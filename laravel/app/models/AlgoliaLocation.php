<?php


use Illuminate\Database\Eloquent\Model;

class AlgoliaLocation extends Model
{
    use \AlgoliaSearch\Laravel\AlgoliaEloquentTrait;

    public $indices = ['prod_eb_locations'];

}