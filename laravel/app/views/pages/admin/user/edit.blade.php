@extends('layouts.default')

@section('content')

<h3>{{ $user ? "User Edit" : "New User" }}</h3>

<div class="row">

    {{ Form::open(array('url' => 'admin/user-save')) }}

    <div class="col-xs-4">

        <h4>General Info</h4>


            <div class="form-group">
                {{ Form::label('fname', 'First Name') }}
                {{ Form::text('fname', ($user ? $user->fname : null), array('class'=>'form-control', 'placeholder'=>'First Name')) }}
            </div>

            <div class="form-group">
                {{ Form::label('lname', 'Last Name') }}
                {{ Form::text('lname', ($user ? $user->lname : null), array('class'=>'form-control', 'placeholder'=>'Last Name')) }}
            </div>

            <div class="form-group">
                {{ Form::label('username', 'Username') }}
                {{ Form::text('username', ($user ? $user->username : null), array('class'=>'form-control', 'placeholder'=>'Username')) }}
            </div>

            <div class="form-group">
                {{ Form::label('email', 'Email') }}
                {{ Form::text('email', ($user ? $user->email : null), array('class'=>'form-control', 'placeholder'=>'Email')) }}
            </div>

            <div class="form-group">
                {{ Form::label('password', 'Password') }}
                {{ Form::text('password', null, array('class'=>'form-control', 'placeholder'=>'')) }}
                @if ($user)
                    <span class="help-block">Entering a password will change the user's password. If you don't want to change the user's password, leave this blank.</span>
                @endif
            </div>

            {{ Form::hidden('userId', ($user ? $user->id : "new")) }}

            <div class="form-group pull-right">
                <a href="/admin/user-list" class="btn btn-default">Cancel</a>
                <button type="submit" class="btn btn-primary">Save User</button>
            </div>


    </div>

    <div class="col-xs-3 col-xs-offset-1">
        <h4>Assigned Roles</h4>
        <ul class="list-unstyled">
        @foreach ($mainRoles as $role)
            <li>{{ Form::checkbox("roles[" . $role['name'] . "]", $role['name'], $role['has']) }} {{ $role['name'] }}</li>
        @endforeach
        </ul>
    </div>

    <div class="col-xs-3">
        <h4>Assigned Stores</h4>
        <ul class="list-unstyled">
        @foreach ($storeRoles as $role)
            <li>{{ Form::checkbox("stores[" . $role['name'] . "]", $role['name'], $role['has']) }} {{ $role['name'] }}</li>
        @endforeach
        </ul>
    </div>

    {{ Form::close() }}

</div>

@stop
