@extends('layouts.default')

@section('content')

<div class="row">
    <div class="col-xs-4">
        <p class="pull-right">
        <a href="/admin/user-edit/new" class="admin-users-add btn btn-primary">Add New User</a>
        </p>
        <table class="users-table table table-striped table-hover">
        @foreach ($users as $user)
            <tr data-userId="{{ $user->id }}">
                <td>{{ $user->lname }}, {{ $user->fname }}</td>
                <td>{{ $user->username }}</td>
            </tr>
        @endforeach
        </table>
    </div>
</div>

@stop
