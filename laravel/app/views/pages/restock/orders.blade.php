@extends('layouts.default')

@section('content')

@include('pages.restock.restockheader')

@include('pages.restock.restocknav')
<br>


<table style="width:100%">
  <thead>
    <tr>
      <td style="text-align:center"><h4>System Restock</h4></td>
      <td style="text-align:center"><h4>Planning Review</h4></td>
      <td style="text-align:center"><h4>Ship/Handling</h4></td>
    </tr>
  </thead>
  <tr>
    @for ($i = 0; $i < $items_count; $i++)
      @if ($orders[$i]['stage'] != 0 && $orders[$i]['stage']!=4)
        <td style="width: 33%">
          @if (count($orders[$i]['data']))

            @include('pages.restock.orderStage')
          @endif
        </td>
      @endif
    @endfor

  </tr>
</table>
<div class="panel panel-default">
  <div class="panel-heading" role="tab" id="headingOne">
    <h4 class="panel-title">
      <a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapse" aria-expanded="true" aria-controls="collapseOne">
        Archived
      </a>
    </h4>
  </div>
    <div id="collapse" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingOne">
    <div class="panel-body panel-restock">
      <br>
      <table class="table table-striped" id="results">
        <tbody>
          @for ($i = 0; $i < count($orders[4]['data']); $i++)
            <div class="well">
              <p>Order ID: {{$orders[4]['data'][$i]['order_id']}}</p>
              <p>Items: {{$orders[4]['data'][$i]['noitems']}}</p>
              <p>Created: {{$orders[4]['data'][$i]['created_on']}}</p>
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

@stop
