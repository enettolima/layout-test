<p>{{ Auth::user()->full_name }} has shared the schedule for Earthbound Store #{{ $currentStore }}, week of {{ $weekOf }} with you.</p>

@if (isset($note) && $note != '')
    <strong>Note from sender:</strong> {{ $note }}
@endif

<p>Link to view schedule:</p>

<p><a href="{{ $link }}">{{ $link }}</a></p>
