@extends('layouts.default')

@section('content')

<h3>Your Passport Preferences</h3>

<br />

<div class="row">

    <div class="col-md-4">


        {{ Form::open(array('url' => 'preferences/update', 'role' => 'form')) }}

        <div class="form-group">

            <label for="defaultStore" class="control-label">Default Store:</label>
            <select name="defaultStore" class="form-control">

                <?php
                foreach (Auth::user()->getStores() as $store) {

                $selected = '';
                if (Auth::user()->defaultStore == $store) {
                $selected = 'selected';
                }

                echo '<option '.$selected.' value="'.$store.'">'.$store.'</option>'."\n";
                }
                ?>

            </select>
            <span class="help-block">
                This controls what your "Current Store" control in the upper right is set to upon login.
            </span>
        </div>

        <br />

        {{ Form::submit('Save Changes', array('class' => 'btn btn-primary btn-block')) }}

        {{ Form::close() }}

    </div>

</div>

@stop
