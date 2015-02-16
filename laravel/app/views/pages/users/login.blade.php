@extends('layouts.default')

@section('content')

    <div class="row">


        <div class="col-sm-4 col-sm-offset-4">

            <h3 class="form-signin-heading">EB Passport Login</h3>

            @if(Session::has('loginMessage'))
                <p><strong>{{ Session::get('loginMessage') }}</strong></p>
            @endif

            {{ Form::open(array('url'=>'users/signin', 'role' => 'form', 'class'=>'form form-signin')) }}

            <div class="form-group">
                {{ Form::text('username', null, array('class'=>'form-control', 'placeholder'=>'Username', 'autofocus'=>'autofocus')) }}
            </div>

            <div class="form-group">
                {{ Form::password('password', array('class'=>'form-control', 'placeholder'=>'Password')) }}
            </div>

            {{ Form::submit('Login', array('class' => 'btn btn-large btn-primary btn-block')) }}

            {{ Form::close() }}

        </div>
    </div>

    <div class="row">
        <div class="col-md-12 top-buffer">
            <center>
                <a href="http://weborder.ebtpassport.com" class="btn btn-sm btn-default">Click Here for Old Passport / WebOrder</a>
            </center>
        </div>
    </div>

    <script src="/js/jquery-git.js"></script>

@stop
