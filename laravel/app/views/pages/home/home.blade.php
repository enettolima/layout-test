@extends('layouts.default')

@section('content')

<div class="jumbotron">

    <h2>Welcome to EBT Passport, {{ Auth::user()->fname }}</h2>

    <p>
        Logged in as {{ Auth::user()->username }}. {{ HTML::link('users/logout', '[Logout]') }}
    </p>

</div>


<p><?php echo Auth::user()->getAuthIdentifier(); ?> </p>

<p><?php echo Auth::user()->getReminderEmail(); ?> </p>

<p><?php echo Auth::user()->getAuthPassword(); ?> </p>

<p><?php var_dump(Auth::user()->getStores()); ?> </p>

<p><?php var_dump(Auth::user()->getStoreRoles()); ?> </p>

<h4>Roles</h4>

<ul>
    <?php
    foreach (Auth::user()->getStoreRoles() as $role) {
        echo "<li>" . $role->name . "</li>";
    }
    ?>
</ul>

<h2>Without Rows:</h2>

<p>
    Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor
    incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis
    nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.
    Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu
    fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in
    culpa qui officia deserunt mollit anim id est laborum.
</p>


<h2>With Rows</h2>

<div class="row">
    <div class="col-md-6">
        <p>
            Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor
            incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis
            nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.
            Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu
            fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in
            culpa qui officia deserunt mollit anim id est laborum.
        </p>
    </div>
    <div class="col-md-6">
        <p>
            Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor
            incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis
            nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.
            Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu
            fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in
            culpa qui officia deserunt mollit anim id est laborum.
        </p>
    </div>
</div>


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

@stop
