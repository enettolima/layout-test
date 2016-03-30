@extends('layouts.default')

@section('content')

@include('pages.restock.restockheader')

@include('pages.restock.restocknav')

<form class="" action="#" id="products-list">

  <br><br>
  <input type="hidden" id="store-id" name="store-id" value={{$store_id}}>
  <div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
    <div class="panel panel-default">
      <div class="panel-heading" role="tab" id="headingOne">
        <h4 class="panel-title">
          <a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
            Restock
          </a>
        </h4>
      </div>
      <div id="collapseOne" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne">
        <div class="panel-body panel-restock">
          <table class="table  table-striped" id="results">
            <tbody>
              @foreach ($response as $prod)
                <tr>
                  <td><img height="50" width="50" class="img-circle img-restock" src="https://ebapi.earthboundtrading.com/pimg/image/{{$prod->item_no}}"></td>
                  <td class="prod-description">
                    <h4>{{$prod->description}}</h4>
                    <strong>Item #:</strong> {{$prod->item_no}}
                    <strong>HQ Quantity:</strong> {{$prod->hq_qty}}
                    <strong>Case Quantity:</strong> {{$prod->case_qty}}
                    <div class="qty-form form-inline">
                      <div class="form-group">
                        <label>Total pieces: {{$prod->qty_box * $prod->case_qty}}</label>
                      </div>
                    </div>
                  </td>
                  <td style="width: 290px">
                    <label>Boxes: <div id="item-qty" class="form-group">
                        <input class="form-control" id="qtybox" name="{{$prod->item_no}}" value="{{$prod->qty_box}}">
                    </div></label>

                  </td>
                  <td>
                    <button type="button" data-item-id="2676" class="update-quantity btn btn-secondary">Update Qty</button>
                    <button type="button" data-item-id="2676" class="btn btn-danger">Remove Item</button>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <div class="panel panel-default">
      <div class="panel-heading" role="tab" id="headingTwo">
        <h4 class="panel-title">
          <a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
            Hardware
          </a>
        </h4>
      </div>
      <div id="collapseTwo" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingTwo">
        <div class="panel-body panel-restock">
          <table class="table  table-striped" id="results">
            <tbody>
              @foreach ($response as $prod)

              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <div class="panel panel-default">
      <div class="panel-heading" role="tab" id="headingThree">
        <h4 class="panel-title">
          <a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
            Signs
          </a>
        </h4>
      </div>
      <div id="collapseThree" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingThree">
        <div class="panel-body panel-restock">
          <table class="table  table-striped" id="results">
            <tbody>
              @foreach ($response as $prod)
                
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</form>


@stop
