@extends('layouts.default')

@section('content')

<h3>Your Passport Settings</h3>

<br />

<div class="row">

    <div class="col-md-4">


        {{ Form::open(array('url' => 'settings/update', 'role' => 'form')) }}

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

        <!--
        <div class="form-group">
            <label for="inputPassword3" class="col-sm-2 control-label">Password</label>
            <div class="col-sm-10">
                <input type="password" class="form-control" id="inputPassword3" placeholder="Password">
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-10">
                <div class="checkbox">
                    <label>
                        <input type="checkbox"> Remember me
                    </label>
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-10">
                <button type="submit" class="btn btn-default">Sign in</button>
            </div>
        </div>
        -->
    {{ Form::close() }}

    </div>

</div>

@stop
