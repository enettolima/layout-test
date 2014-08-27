@extends('layouts.default')

@section('content')

<h3>Your Passport Preferences</h3>

<?php 
if ($errors->count() > 0) {

    echo "<div class='alert alert-danger'>";
        echo "<h4>There were errors with your submission:</h4>";
        echo "<ul>";
            foreach ($errors->getMessages() as $key) {
                foreach ($key as $error) {
                    echo "<li>$error</li>";
                }
            }
        echo "</ul>";
    echo "</div>";
}
?>

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

        <div class="form-group">
            <label for="preferredEmail" class="control-label">Preferred Contact Email:</label>
            {{ Form::text('preferredEmail', (Input::old('preferredEmail') ? Input::old('preferredEmail') : Auth::user()->preferred_email), array('class'=>'form-control', 'placeholder'=>'someone@example.com')) }}
            <span class="help-block">
                Please provide the email address which you'd like Earthbound-based updates to be sent to.
            </span>
        </div>

        {{ Form::submit('Save Changes', array('class' => 'btn btn-primary btn-block')) }}

        {{ Form::close() }}

    </div>

</div>

@stop
