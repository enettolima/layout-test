@extends('layouts.default')

@section('content')

<div class="row">
    <div class="col-xs-7">
        <h3>Product Information Management</h3>

        <div class="form-inline">
            <div class="form-group">
                <input type="text" class="form-control" id="product-search-field" placeholder="Enter Item Number">
            </div>
            <button type="button" id="product-search-button" class="btn btn-primary">Go</button>
        </div>

        <span id="searching" style="display:none;"><em>Searching </em><img src="/images/ajax-loader-arrows.gif"></span>

        <span id="not-found" style="display:none;"><em>Not Found!</em></span>

        <div style="display:none;" role="form" class="form" id="product-info-form">
            <div class="form-group">
                <label for="name">Retail Pro Name</label>
                <input class="form-control" disabled="disabled" type="text" name="rp-name" id="rp-name" value="">
            </div>
            <div class="form-group">
                <label for="name">Retail Pro Description</label>
                <input class="form-control" disabled="disabled" type="text" name="rp-description" id="rp-description" value="">
            </div>

            <h4>PIMS Data</h4>
            <div class="form-group">
                <label for="name">Field1</label>
                <input class="form-control" type="text" name="pims-field1" id="pims-field1" value="">
            </div>

            <button id="save-pi" class="btn btn-primary" type="button">Save Changes to PIMS Data</button>

            <span id="save-pi-results" class="" style="display:none;"></span>
        </div>
    </div>
</div>

@stop
