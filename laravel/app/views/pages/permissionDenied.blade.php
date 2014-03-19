@extends('layouts.default')

@section('content')

    <h2 class="text-danger">Permission Denied</h2>

    <p>You don't have sufficient permissions to complete this action.</p>

    <?php
        if (isset($moreInfo) && $moreInfo != '') {
            echo '<p>'.$moreInfo.'</p>';
        }
    ?>

@stop
