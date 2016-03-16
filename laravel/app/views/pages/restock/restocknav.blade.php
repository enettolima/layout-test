<div class="btn-group btn-group-justified" role="group">
    <a href="/restock/browse" class="btn btn-default <?php echo Request::is('restock/browse*') ?  'btn-primary' : '' ?>" role="button">Browse</a>
    <a href="/restock/carts" class="btn btn-default <?php echo Request::is('restock/carts*') ?  'btn-primary' : '' ?>" role="button">Open Carts</a>
    <a href="/restock/orders" class="btn btn-default <?php echo Request::is('restock/orders*') ?  'btn-primary' : '' ?>" role="button">Orders</a>
</div>