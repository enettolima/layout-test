@extends('layouts.default')

@section('content')

<div class="doc-search">

<h3>Earthbound Documents</h3>
<form role="form">
  <div class="form-group">
    <input type="text" class="form-control" id="searchString" placeholder="Search for Something" autofocus>
  </div>
  <button type="submit" class="btn btn-default">Search</button>
</form>

<ul id="results">
    <li>
        <h4><a href="asdf">Filename - Something Something.pdf</a></h4>
        <ul>
            <li><strong>File Date:</strong> 2/1/2014</li>
            <li>Lorem ipsum dolor sit amet, <em class="bg-success text-success">consectetur adipisicing elit</em>, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</li>
        </ul>
    </li>
    <li>
        <h4><a href="asdf">Filename - Something Something.pdf</a></h4>
        <ul>
            <li>Date: 2/1/2014</li>
            <li>Lorem ipsum dolor sit amet, <em>consectetur adipisicing elit</em>, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</li>
        </ul>
    </li>
    <li>
        <h4><a href="asdf">Filename - Something Something.pdf</a></h4>
        <ul>
            <li>Date: 2/1/2014</li>
            <li>Lorem ipsum dolor sit amet, <em>consectetur adipisicing elit</em>, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</li>
        </ul>
    </li>
</ul>
</div>

@stop
