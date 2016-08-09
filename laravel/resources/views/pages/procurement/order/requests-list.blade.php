<div class="row">
    <div class="col-lg-12">
        <div class="wrapper wrapper-content animated fadeInUp">

            <div class="ibox">
                <div class="ibox-title">
                    <h5>All Orders assigned to this account</h5>
                    <div class="ibox-tools">
                        <a href="#" onclick="createorder_step1()" class="btn btn-primary btn-xs" data-toggle="modal" data-target="#new-order">Create new order</a>
                    </div>
                </div>
                <div class="ibox-content">
                    <div class="row m-b-sm m-t-sm">
                        <div class="col-md-1">
                            <button type="button" id="loading-example-btn" class="btn btn-white btn-sm" ><i class="fa fa-refresh"></i> Refresh</button>
                        </div>
                        <div class="col-md-11">
                            <div class="input-group"><input type="text" placeholder="Search" class="input-sm form-control"> <span class="input-group-btn">
                                <button type="button" class="btn btn-sm btn-primary"> Go!</button> </span></div>
                        </div>
                    </div>

                    <div class="project-list">

                        <table class="table table-hover">
                            <tbody>
                            <tr>
                                <td class="project-status">
                                    <span class="label label-primary">Active</span>
                                </td>
                                <td class="project-title">
                                    <a href="/order/details?order={{ isset($order) ? $order : 'request' }}">935-08092016</a>
                                    <br/>
                                    <small>Created 08.09.2016</small>
                                </td>
                                <td class="project-completion">
                                        <small>Completion with: 48%</small>
                                        <div class="progress progress-mini">
                                            <div style="width: 48%;" class="progress-bar"></div>
                                        </div>
                                </td>
                                <td class="project-people">
                                    <a href=""><img alt="image" class="/img-circle" src="/img/a3.jpg"></a>
                                    <a href=""><img alt="image" class="/img-circle" src="/img/a1.jpg"></a>
                                    <a href=""><img alt="image" class="/img-circle" src="/img/a2.jpg"></a>
                                    <a href=""><img alt="image" class="/img-circle" src="/img/a4.jpg"></a>
                                    <a href=""><img alt="image" class="/img-circle" src="/img/a5.jpg"></a>
                                </td>
                                <td class="project-actions">

                                    <a href=/order/details class="btn btn-white btn-sm"><i class="fa fa-pencil"></i> Edit </a>
                                </td>
                            </tr>
                            <tr>
                                <td class="project-status">
                                    <span class="label label-primary">Active</span>
                                </td>
                                <td class="project-title">
                                    <a href="/order/details?order={{ isset($order) ? $order : 'request' }}">345-08092016</a>
                                    <br/>
                                    <small>Created 08.09.2016</small>
                                </td>
                                <td class="project-completion">
                                    <small>Completion with: 28%</small>
                                    <div class="progress progress-mini">
                                        <div style="width: 28%;" class="progress-bar"></div>
                                    </div>
                                </td>
                                <td class="project-people">
                                    <a href=""><img alt="image" class="/img-circle" src="/img/a7.jpg"></a>
                                    <a href=""><img alt="image" class="/img-circle" src="/img/a6.jpg"></a>
                                    <a href=""><img alt="image" class="/img-circle" src="/img/a3.jpg"></a>
                                </td>
                                <td class="project-actions">

                                    <a href=/order/details class="btn btn-white btn-sm"><i class="fa fa-pencil"></i> Edit </a>
                                </td>
                            </tr>
                            <tr>
                                <td class="project-status">
                                    <span class="label label-default">Unactive</span>
                                </td>
                                <td class="project-title">
                                    <a href="/order/details?order={{ isset($order) ? $order : 'request' }}">342-08072016</a>
                                    <br/>
                                    <small>Created 08.07.2016</small>
                                </td>
                                <td class="project-completion">
                                    <small>Completion with: 8%</small>
                                    <div class="progress progress-mini">
                                        <div style="width: 8%;" class="progress-bar"></div>
                                    </div>
                                </td>
                                <td class="project-people">
                                    <a href=""><img alt="image" class="/img-circle" src="/img/a5.jpg"></a>
                                    <a href=""><img alt="image" class="/img-circle" src="/img/a3.jpg"></a>
                                </td>
                                <td class="project-actions">

                                    <a href=/order/details class="btn btn-white btn-sm"><i class="fa fa-pencil"></i> Edit </a>
                                </td>
                            </tr>
                            <tr>
                                <td class="project-status">
                                    <span class="label label-primary">Active</span>
                                </td>
                                <td class="project-title">
                                    <a href="/order/details?order={{ isset($order) ? $order : 'request' }}">567-08062016</a>
                                    <br/>
                                    <small>Created 08.06.2016</small>
                                </td>
                                <td class="project-completion">
                                    <small>Completion with: 83%</small>
                                    <div class="progress progress-mini">
                                        <div style="width: 83%;" class="progress-bar"></div>
                                    </div>
                                </td>
                                <td class="project-people">
                                    <a href=""><img alt="image" class="/img-circle" src="/img/a2.jpg"></a>
                                    <a href=""><img alt="image" class="/img-circle" src="/img/a3.jpg"></a>
                                    <a href=""><img alt="image" class="/img-circle" src="/img/a1.jpg"></a>
                                    <a href=""><img alt="image" class="/img-circle" src="/img/a7.jpg"></a>
                                </td>
                                <td class="project-actions">

                                    <a href=/order/details class="btn btn-white btn-sm"><i class="fa fa-pencil"></i> Edit </a>
                                </td>
                            </tr>
                            <tr>
                                <td class="project-status">
                                    <span class="label label-primary">Active</span>
                                </td>
                                <td class="project-title">
                                    <a href="/order/details?order={{ isset($order) ? $order : 'request' }}">498-08062016</a>
                                    <br/>
                                    <small>Created 08.06.2016</small>
                                </td>
                                <td class="project-completion">
                                    <small>Completion with: 97%</small>
                                    <div class="progress progress-mini">
                                        <div style="width: 97%;" class="progress-bar"></div>
                                    </div>
                                </td>
                                <td class="project-people">
                                    <a href=""><img alt="image" class="/img-circle" src="/img/a4.jpg"></a>
                                </td>
                                <td class="project-actions">

                                    <a href=/order/details class="btn btn-white btn-sm"><i class="fa fa-pencil"></i> Edit </a>
                                </td>
                            </tr>
                            <tr>
                                <td class="project-status">
                                    <span class="label label-primary">Active</span>
                                </td>
                                <td class="project-title">
                                    <a href="/order/details?order={{ isset($order) ? $order : 'request' }}">115-08062016</a>
                                    <br/>
                                    <small>Created 08.06.2016</small>
                                </td>
                                <td class="project-completion">
                                    <small>Completion with: 48%</small>
                                    <div class="progress progress-mini">
                                        <div style="width: 48%;" class="progress-bar"></div>
                                    </div>
                                </td>
                                <td class="project-people">
                                    <a href=""><img alt="image" class="/img-circle" src="/img/a1.jpg"></a>
                                    <a href=""><img alt="image" class="/img-circle" src="/img/a2.jpg"></a>
                                    <a href=""><img alt="image" class="/img-circle" src="/img/a4.jpg"></a>
                                    <a href=""><img alt="image" class="/img-circle" src="/img/a5.jpg"></a>
                                </td>
                                <td class="project-actions">

                                    <a href=/order/details class="btn btn-white btn-sm"><i class="fa fa-pencil"></i> Edit </a>
                                </td>
                            </tr>
                            <tr>
                                <td class="project-status">
                                    <span class="label label-primary">Active</span>
                                </td>
                                <td class="project-title">
                                    <a href="/order/details?order={{ isset($order) ? $order : 'request' }}">756-08052016</a>
                                    <br/>
                                    <small>Created 08.05.2016</small>
                                </td>
                                <td class="project-completion">
                                    <small>Completion with: 28%</small>
                                    <div class="progress progress-mini">
                                        <div style="width: 28%;" class="progress-bar"></div>
                                    </div>
                                </td>
                                <td class="project-people">
                                    <a href=""><img alt="image" class="/img-circle" src="/img/a7.jpg"></a>
                                    <a href=""><img alt="image" class="/img-circle" src="/img/a6.jpg"></a>
                                    <a href=""><img alt="image" class="/img-circle" src="/img/a3.jpg"></a>
                                </td>
                                <td class="project-actions">

                                    <a href=/order/details class="btn btn-white btn-sm"><i class="fa fa-pencil"></i> Edit </a>
                                </td>
                            </tr>
                            <tr>
                                <td class="project-status">
                                    <span class="label label-default">Unactive</span>
                                </td>
                                <td class="project-title">
                                    <a href="/order/details?order={{ isset($order) ? $order : 'request' }}">874-08042016</a>
                                    <br/>
                                    <small>Created 08.04.2016</small>
                                </td>
                                <td class="project-completion">
                                    <small>Completion with: 8%</small>
                                    <div class="progress progress-mini">
                                        <div style="width: 8%;" class="progress-bar"></div>
                                    </div>
                                </td>
                                <td class="project-people">
                                    <a href=""><img alt="image" class="/img-circle" src="/img/a5.jpg"></a>
                                    <a href=""><img alt="image" class="/img-circle" src="/img/a3.jpg"></a>
                                </td>
                                <td class="project-actions">

                                    <a href=/order/details class="btn btn-white btn-sm"><i class="fa fa-pencil"></i> Edit </a>
                                </td>
                            </tr>
                            <tr>
                                <td class="project-status">
                                    <span class="label label-primary">Active</span>
                                </td>
                                <td class="project-title">
                                    <a href="/order/details?order={{ isset($order) ? $order : 'request' }}">328-07312016</a>
                                    <br/>
                                    <small>Created 07.31.2016</small>
                                </td>
                                <td class="project-completion">
                                    <small>Completion with: 83%</small>
                                    <div class="progress progress-mini">
                                        <div style="width: 83%;" class="progress-bar"></div>
                                    </div>
                                </td>
                                <td class="project-people">
                                    <a href=""><img alt="image" class="/img-circle" src="/img/a2.jpg"></a>
                                    <a href=""><img alt="image" class="/img-circle" src="/img/a3.jpg"></a>
                                    <a href=""><img alt="image" class="/img-circle" src="/img/a1.jpg"></a>
                                </td>
                                <td class="project-actions">

                                    <a href=/order/details class="btn btn-white btn-sm"><i class="fa fa-pencil"></i> Edit </a>
                                </td>
                            </tr>
                            <tr>
                                <td class="project-status">
                                    <span class="label label-primary">Active</span>
                                </td>
                                <td class="project-title">
                                    <a href="/order/details?order={{ isset($order) ? $order : 'request' }}">955-07282016</a>
                                    <br/>
                                    <small>Created 07.28.2016</small>
                                </td>
                                <td class="project-completion">
                                    <small>Completion with: 97%</small>
                                    <div class="progress progress-mini">
                                        <div style="width: 97%;" class="progress-bar"></div>
                                    </div>
                                </td>
                                <td class="project-people">
                                    <a href=""><img alt="image" class="/img-circle" src="/img/a4.jpg"></a>
                                </td>
                                <td class="project-actions">

                                    <a href=/order/details class="btn btn-white btn-sm"><i class="fa fa-pencil"></i> Edit </a>
                                </td>
                            </tr>
                            <tr>
                                <td class="project-status">
                                    <span class="label label-primary">Active</span>
                                </td>
                                <td class="project-title">
                                    <a href="/order/details?order={{ isset($order) ? $order : 'request' }}">935-07102016</a>
                                    <br/>
                                    <small>Created 07.10.2016</small>
                                </td>
                                <td class="project-completion">
                                    <small>Completion with: 28%</small>
                                    <div class="progress progress-mini">
                                        <div style="width: 28%;" class="progress-bar"></div>
                                    </div>
                                </td>
                                <td class="project-people">
                                    <a href=""><img alt="image" class="/img-circle" src="/img/a7.jpg"></a>
                                    <a href=""><img alt="image" class="/img-circle" src="/img/a6.jpg"></a>
                                    <a href=""><img alt="image" class="/img-circle" src="/img/a3.jpg"></a>
                                </td>
                                <td class="project-actions">

                                    <a href=/order/details class="btn btn-white btn-sm"><i class="fa fa-pencil"></i> Edit </a>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<form class="modal multi-step inmodal fade" id="new-order">
  <div class="modal-dialog modal-lg">
      <div class="modal-content">

          <div class="modal-header">
              <h4 class="modal-title step-1" data-step="1">Create new order</h4>
              <h4 class="modal-title step-2" data-step="2">Confirm Order</h4>
          </div>
          <div class="modal-body step step-1">
            <ul class="list-group" id="new-order-vendor">
                  <li class="list-group-item" value="340"><span class="badge">340</span><a data-toggle="tab" href="#contact-1" class="client-link">340 - Li & Fung</a></li>
                  <li class="list-group-item active" value="935"><span class="badge">935</span><a data-toggle="tab" href="#contact-2" class="client-link">935 - INDIA 360 CLOTHING</a></li>
            </ul>
          </div>
          <div class="modal-body step step-2">
            <h4>Order Number</h4>
            <input id="new-order-number" type="text" placeholder="Order Number" class="form-control">
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            <button type="button" class="btn btn-default step step-1" data-step="1" onclick="createorder_step2()">Next</button>
            <button type="button" class="btn btn-default step-2" onclick="createorder_finish()" data-step="2">Create Order</button>
          </div>
      </div>
  </div>
</form>
<script>
$( document ).ready(function() {
  function createorder_finish(){
    console.log('finish');
  }

  createorder_step1 = function() {
    $("#new-order").trigger('next.m.1');
  }

  createorder_step2 = function() {
    var d = new Date();
    var month = d.getMonth()+1;
    var day = d.getDate();
    var year = d.getFullYear();
    var ordernum = $('#new-order-vendor li.active').attr('value') + "-" + ((''+month).length<2 ? '0' : '') + month + ((''+day).length<2 ? '0' : '') + day + year;
    $("#new-order-number").val(ordernum);
    $("#new-order").trigger('next.m.2');
  }
});
</script>
