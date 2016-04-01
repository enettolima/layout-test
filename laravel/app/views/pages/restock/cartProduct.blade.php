<tr>
  <td><img height="50" width="50" class="img-circle img-restock" src="https://ebapi.earthboundtrading.com/pimg/image/{{$prod['item_no']}}"></td>
  <td class="prod-description">
    <h4>{{$prod['description']}}</h4>
    <strong>Item #:</strong> {{$prod['item_no']}}
    <strong>HQ Quantity:</strong> {{$prod['hq_qty']}}
    <strong>Case Quantity:</strong> {{$prod['case_qty']}}
    <div class="qty-form form-inline">
      <div class="form-group">
        <label>Total pieces: {{$prod['qty_box'] * $prod['case_qty']}}</label>
      </div>
    </div>
  </td>
  <td style="width: 290px">
    <label>Boxes: <div id="item-qty" class="form-group">
      <input class="form-control" id="qtybox" name="{{$prod['item_no']}}" value="{{$prod['qty_box']}}">
    </div></label>
  </td>
  <td>
    <button type="button" data-item-id="" class="update-quantity btn btn-secondary">Update Qty</button>
    <button type="button" data-item-id="{{$store_id}}-{{$prod['item_no']}}" class="remove-item btn btn-danger">Remove Item</button>
  </td>
</tr>
