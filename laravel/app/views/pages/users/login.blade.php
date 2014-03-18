@extends('layouts.default')

@section('content')

    <div class="login-container">

        <h3 class="form-signin-heading">Passport Login</h3>

        @if(Session::has('loginMessage'))
            <p><strong>{{ Session::get('loginMessage') }}</strong></p>
        @endif

        {{ Form::open(array('url'=>'users/signin', 'role' => 'form', 'class'=>'form-signin')) }}

        <div class="form-group">
            {{ Form::text('username', null, array('class'=>'form-control', 'placeholder'=>'Username')) }}
        </div>

        <div class="form-group">
            {{ Form::password('password', array('class'=>'form-control', 'placeholder'=>'Password')) }}
        </div>

        {{ Form::submit('Login', array('class' => 'btn btn-large btn-primary btn-block')) }}

        {{ Form::close() }}

    </div>

    <script src="/js/jquery-git.js"></script>

@stop
