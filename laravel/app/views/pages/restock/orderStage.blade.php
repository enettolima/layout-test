<div class="well">
  <i class="fa fa-pencil"></i>
  <p>Order ID: {{$orders[$i]['data'][0]['order_id']}}</p>
  <p>Items: {{$orders[$i]['data'][0]['noitems']}}</p>
  <p>Created: {{$orders[$i]['data'][0]['created_on']}}</p>
  <div class="slider slider-horizontal" >
    <div class="progress">
      @if ($orders[$i]['stage'] == 1)
        <div class="progress-bar progress-bar-danger" role="progressbar" aria-valuenow="30"
          aria-valuemin="0" aria-valuemax="100" style="width:30%">
            30%
        </div>
      @elseif ($orders[$i]['stage'] == 2)
        <div class="progress-bar progress-bar-warning" role="progressbar" aria-valuenow="70"
          aria-valuemin="0" aria-valuemax="100" style="width:70%">
            70%
        </div>
      @elseif ($orders[$i]['stage'] == 3)
        <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="90"
          aria-valuemin="0" aria-valuemax="100" style="width:90%">
            90%
        </div>
      @else
        <div class="progress-bar" role="progressbar" aria-valuenow="100"
          aria-valuemin="0" aria-valuemax="100" style="width:100%">
            100% (Completed)
        </div>
      @endif
    </div>
  </div>
</div>
