@extends('layouts.default')

@section('content')

<div class="row">
    <div class="col-xs-4">
        <h4>Users (from Retail Pro):</h4>
        <table class="users-table table table-striped table-hover">
        @foreach ($users as $user)
            <tr data-userId="{{ $user->id }}">
                <td>{{ $user->username }}</td>
                <td>{{ $user->full_name }}</td>
            </tr>
        @endforeach
        </table>
    </div>
</div>

@stop
