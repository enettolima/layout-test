@extends('layouts.default')

@section('content')

<div class="jumbotron">
    <h2>Welcome to EBT Passport</h2>
    <p>
        Logged in as {{ Auth::user()->email }}. {{ HTML::link('users/logout', '[Logout]') }}
    </p>

</div>

<div class="row">
<?php

/*
$password = Hash::make('secret');

if (Hash::check('secret', $password)) {
    echo "Hey that worked!";
}
*/

/*
$User = User::findOrFail(1);
$User->password = Hash::make('secret');
$User->save();
var_dump($User);
*/

/*
if (Auth::attempt(array('email' => 'chad.davis@gmail.com', 'password' => 'secreto'))) {
    echo "Good password";
} else {
    echo "Nope";
}
*/

/*
if (Auth::check()) {
    echo "User " . Auth::user()->email . " is logged in";
    Auth::logout();
} else {
    echo "User is not logged in";
}
*/

?>
</div>

@stop
