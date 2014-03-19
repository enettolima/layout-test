@extends('layouts.default')

@section('content')

    <h2>Permission Denied</h2>

    <p>You don't have sufficient permissions to complete this action.</p>

    <?php
        if (isset($moreInfo) && $moreInfo != '') {
            echo '<p class="bg-danger">'.$moreInfo.'</p>';
        }
    ?>

@stop
