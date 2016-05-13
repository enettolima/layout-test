@extends('layouts.default')

@section('content')

@include('pages.restock.restockheader')

@include('pages.restock.restocknav')
<br>


<table style="width:100%" class="table">
  <thead>
    <tr>
      <td style="text-align:center"><h4>System Restock</h4></td>
      <td style="text-align:center"><h4>Planning Review</h4></td>
      <td style="text-align:center"><h4>Ship/Handling</h4></td>
    </tr>
  </thead>

  @for ($i = 0; $i < count($orders); $i++)

    <tr>
      @if ($orders[$i]['max_stage'] == 1)
        <td style="width: 33%">@include('pages.restock.orderStage')</td>
        <td style="width: 33%"></td>
        <td style="width: 33%"></td>
      @elseif ($orders[$i]['max_stage'] == 2)
        <td style="width: 33%"></td>
        <td style="width: 33%">@include('pages.restock.orderStage')</td>
        <td style="width: 33%"></td>
      @else
        <td style="width: 33%"></td>
        <td style="width: 33%"></td>
        <td style="width: 33%">@include('pages.restock.orderStage')</td>
      @endif
    </tr>
  @endfor


  <!--@for ($j = 0; $j < $order_count; $j++)
    @if($orders[$j]['stage']>0 && $orders[$j]['stage']<4 && count($orders[$j]['data'])>0)
      <tr>
        @for ($i = 0; $i < $order_stage_count; $i++)
          @if ($orders[$i]['stage'] != 0 && $orders[$i]['stage']!=4)
            <td style="width: 33%">
              @if (count($orders[$i]['data']))
                @include('pages.restock.orderStage')
              @endif
            </td>
          @endif
        @endfor
      </tr>
    @endif
  @endfor-->
</table>
<div class="panel panel-default">
  <div class="panel-heading" role="tab" id="headingOne">
    <h4 class="panel-title">
      <a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapse" aria-expanded="true" aria-controls="collapseOne">
        Archived
      </a>
      <input type="hidden" id="store-id" value={{$store_id}}>
    </h4>
  </div>
    <div id="collapse" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingOne">
    <div class="panel-body panel-restock">
      <br>
      <table class="table table-striped" id="results">
        <tbody>
          @for ($i = 0; $i < count($stages[4]['data']); $i++)
            <div class="well order-stages">
              <i class="fa fa-list load-products" data-toggle="modal" data-target=".product-list-modal"></i>
              <input type="hidden" id="order_id" value="{{$stages[4]['data'][$i]['order_id']}}-{{$stages[4]['data'][$i]['max_stage']}}">
              Order ID: {{$stages[4]['data'][$i]['order_id']}}<br>
              Items: {{$stages[4]['data'][$i]['noitems']}}<br>
              Created: {{$stages[4]['data'][$i]['created_on']}}<br>
              Type: {{$stages[4]['data'][$i]['type_name']}}<br>
              <div class="slider slider-horizontal" >
                <div class="progress">
                  <div class="progress-bar" role="progressbar" aria-valuenow="100"
                    aria-valuemin="0" aria-valuemax="100" style="width:100%">
                      100% (Completed)
                  </div>
                </div>
              </div>
            </div>
          @endfor
        </tbody>
      </table>
    </div>
  </div>
</div>

<div class="modal fade product-list-modal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="gridSystemModalLabel">Modal title</h4>
      </div>
      <div class="modal-body">
        <table class="table table-striped" id="order-products">

        </table>
      </div>
    </div>
  </div>
</div>

@stop


<!--<tbody>
  <tr>
    <td><img height="50" width="50" class="img-circle img-restock" src="https://ebapi.earthboundtrading.com/pimg/image/4322"></td>
    <td class="prod-description">
      <h4>Blabla</h4>
      <strong>Item #:</strong> 23
      <strong>HQ Quantity:</strong> 1554
      <strong>Case Quantity:</strong> 12
      <div class="qty-form form-inline">
        <div class="form-group">
          <label>Total pieces: 223</label>
        </div>
      </div>
    </td>
    <td style="width: 290px">
      <label>Boxes: <div id="item-qty" class="form-group">
        <input class="form-control" id="qtybox" name="prod" value="qtybox">
      </div></label>
    </td>
    <td>
      <button type="button" data-item-id="" class="update-quantity btn btn-secondary">Update Qty</button>
      <button type="button" data-item-id="{{$store_id}}-prod" class="remove-item btn btn-danger">Remove Item</button>
    </td>
  </tr>
</tbody>-->
