@extends('layouts.default')

@section('content')

<div class="row">
    <div class="col-xs-6">
        <h3>Tax Exempt Sale Form</h3>

		@if ($errors->has())
			@include('includes.errors')
		@endif

        <p>
            When you process a tax-exempt sale you must use this form to upload the customer's scanned documents to support the sale.
		</p>

		<h5>To Finalize a Tax Exempt Sale:</h5>
		<ol>
			<li>Scan and save the customer's Exemption Form</li>
			<li>Scan and save the customer's ID. (Driver's license, state-issued id)</li>
			<li>Complete the form below, uploading the scanned images.</li>
		</ol>

        <form role="form" class="form" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="receiptNum">Receipt #</label>
                <input type="text" class="form-control" name="receiptNum">
            </div>

            @foreach ($filesDef as $key=>$file)
                <div class="form-group">
                    <label for="{{ $key }}">Upload Image: {{ $file['label'] }}</label>
                    <input type="file" class="file" id="{{ $key }}" name="{{ $key }}" data-show-upload="false">
                </div>
            @endforeach
            <button type="submit" class="btn btn-primary">Submit Exempt Documentation</button>
        </form>
        <br />
        <span id="searching" class="hidden"><em>Searching </em><img src="/images/ajax-loader-arrows.gif"></span>
        <blockquote id="results" class="hidden"></blockquote>
    </div>

    <div class="col-xs-5 col-xs-offset-1 well">
        <h4>Exempt Form History for {{ PassportHelper::getStoreString() }}</h4>
        <h5>(Last {{ $historyDays }} Days)</h5>


            @if (count($lastSubmissions) > 0)
                <table class="table">
                    <tr>
                        <th>Date</th>
                        <th>Submitter</th>
                        <th>Receipt #</th>
                    </tr>

                    @foreach ($lastSubmissions as $sub)
                    <tr>
                        <td>
                            {{ date("m/d/Y g:ia", strtotime($sub->created_at)) }}
                        </td>
                        <td>
                            {{ $sub->empl_id }}
                        </td>
                        <td>
                            {{ $sub->receipt_num }}
                        </td>
                    </tr>
                    @endforeach
                </table>
            @else
                <p><em>No exempt forms have been submitted for this store.</em></p>
            @endif



    </div>

</div>

@stop

