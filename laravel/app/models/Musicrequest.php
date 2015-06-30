<?php

class Musicrequest extends Eloquent {

    public function comments()
    {
        return $this->morphMany('Comment', 'commentable');
    }
}
