@extends('layouts.default')

@section('content')

@include('pages.restock.restockheader')

@include('pages.restock.restocknav')

<form class="" action="#" id="products-list">

  <br><br>
  <input type="hidden" id="store-id" name="store-id" value={{$store_id}}>
  <div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">


    @for ($i = 0; $i < $total_count; $i++)

      <div class="panel panel-default">
        <div class="panel-heading" role="tab" id="headingOne">
          <h4 class="panel-title">
            <a role="button" id="collapse-title" data-toggle="collapse" data-parent="#accordion" href="#collapse{{$i}}" aria-expanded="true" aria-controls="collapseOne">
              {{$cart_info[$i]['name']}} - {{$cart_info[$i]['count']}}<!--({{$cart_info[$i]['count']}})-->
            </a>
          </h4>
        </div>

        @if ($i === 0)
          <div id="collapse{{$i}}" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne">
        @else
          <div id="collapse{{$i}}" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingOne">
        @endif

          <div class="panel-body panel-restock">
            <table class="table table-striped" id="cart-results">
              <tbody>
                @foreach ($cart_info[$i]['data'] as $prod)
                  @include('pages.restock.cartProduct')
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
    @endfor
  </div>
</form>


@stop
