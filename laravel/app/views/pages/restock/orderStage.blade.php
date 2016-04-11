<div class="well order-stages">
  <i class="fa fa-list load-products" data-toggle="modal" data-target=".product-list-modal"></i>
  <input type="hidden" id="order_id" value="{{$orders[$i]['order_id']}}-{{$orders[$i]['max_stage']}}">
  Order ID: {{$orders[$i]['order_id']}}<br>
  Items: {{$orders[$i]['noitems']}}<br>
  Created: {{$orders[$i]['created_on']}}<br>
  Type: {{$orders[$i]['type_name']}}<br>
  <div class="slider slider-horizontal" >
    <div class="progress">
      @if ($orders[$i]['max_stage'] == 1)
        <div class="progress-bar progress-bar-danger" role="progressbar" aria-valuenow="30"
          aria-valuemin="0" aria-valuemax="100" style="width:30%">
            30%
        </div>
      @elseif ($orders[$i]['max_stage'] == 2)
        <div class="progress-bar progress-bar-warning" role="progressbar" aria-valuenow="70"
          aria-valuemin="0" aria-valuemax="100" style="width:70%">
            70%
        </div>
      @elseif ($orders[$i]['max_stage'] == 3)
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
