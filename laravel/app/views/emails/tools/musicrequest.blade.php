<h4>Request Details:</h4>
<dl>
	<dt>Requestor</dt>
	<dd>
		{{ $request['empid'] }}
		{{ $request['empname'] }}
	</dd>
</dl>
<dl>
	<dt>Request</dt>
	<dd class="well">
		{{ nl2br($request['request']) }}
	</dd>
</dl>
