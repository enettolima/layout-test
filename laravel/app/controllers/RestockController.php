<?php

class RestockController extends BaseController
{
    private $maintenance = false;
    private $store_id = 0;
    private $error    = false;
    private $error_msg= "";
    private $order_id = 0;
    private $locked   = 0;
    private $stage    = 0;
    private $items_count = 0;
    /* Require Auth on Everything Here */
    public function __construct()
    {
        $this->beforeFilter('auth', array());
        $this->store_id = Session::get('storeContext');
        //Log::info("Store ID on construct ".$this->store_id);
        $this->getCartStatus();
        $this->maintenance = false;
    }

    public function getIndex()
    {
      if($this->maintenance){
        return View::make('pages.restock.maintenance');
        return View::make('pages.maintenance',
          array(
            "title" => "Reorder is currently down for maintenance.",
            "message" => "Thank you for your patience and sorry for the inconvenience."
        ));
      }else{
        return Redirect::to('/restock/browse');
      }
    }

    public function getBrowse()
    {
        return View::make(
            'pages.restock.browse',
            array(
                'error' => $this->error,
                'error_msg' => $this->error_msg,
                'cart_id' => $this->order_id,
                'items_count' => $this->items_count,
                'store_id' => $this->store_id,
                'extraJS' => array(
                    '/js/restock/restock.js',
                    '/js/jstree.min.js',
                ),
      					'extraCSS' => array (
      						'/css/jstree.css'
      					)
            )
        );
    }

    private function getCartStatus(){
      try{
        $api = new EBTAPI;
        $res = $api->get("/restock/cart/".$this->store_id."/status");
        Log::info("Status API Call",array('response' => $res));
        if($res->error_code>0 && $res->error_code==404){
          $this->items_count = 0;
          //$this->error = true;
          //$this->error_msg = "No active orders were found at this moment!";
        }else{
          //$this->order_id = $res->data->order_id;
          $this->items_count = $res->data->total;
        }
      } catch (Exception $e) {
        //Cart not found
        $this->error = true;
        $this->error_msg = "Cart is currently unavailable.";
      }
      //Log::info("Cart status -- error code ".$res->error_code);//." -- order_id ".$res->data->order_id." -- locked ".$res->data->locked);
      //return Response::json($res);
    }

    public function getCarts()
    {
      $response = array();
      try{
        //Getting all the products from a store's cart
        $api = new EBTAPI;
        $res = $api->get("/restock/cart/".$this->store_id."/product");

        //Getting all the order stages
        $api = new EBTAPI;
        $types = $api->get("/restock/order-type");

        //Looping to all the types and building the array for blade to use
        $order_type = array();
        for ($i=0; $i < count($types->data); $i++) {
          $prods = array();
          $count = 0;
          for ($j=0; $j < count($res->data); $j++) {
            if($res->data[$j]->type==$types->data[$i]->order_type){
              foreach ($res->data[$j] as $key => $value) {
                $prods[$j][$key] = $value;

              }
              $count++;
            }
          }
          $order_type[$i]['type'] = $types->data[$i]->order_type;
          $order_type[$i]['name'] = $types->data[$i]->type_name;
          $order_type[$i]['count'] = $count;
          $order_type[$i]['data'] = $prods;
        }

        //$this->items_count = count($res->data);
        $response = $res->data;
      } catch (Exception $e) {
        //Cart not found
        $this->error = true;
        $this->error_msg = "Cart is currently unavailable.";
      }

      //Log::info("Order Type array",$order_type);
      return View::make('pages.restock.carts',
        array(
          'error' => $this->error,
          'error_msg' => $this->error_msg,
          'store_id' => $this->store_id,
          'items_count' => $this->items_count,
          'cart_info' => $order_type,
          'total_count' => count($order_type),
          'extraJS' => array(
            '/js/restock/restock.js'
          )
        )
      );
    }

    public function getOrders()
    {
      try{
        ////////End test
        //Getting all the order stages
        $api = new EBTAPI;
        $types = $api->get("/restock/order-stages");
        /* Stages are
        0 = Store Order
        1 = System Restock
        2 = Planning Review
        3 = Ship/Handling
        4 = Archived
        */
        //Getting all the orders from this store
        $api = new EBTAPI;
        $res = $api->get("/restock/order/".$this->store_id);

        $pending = 0;
        $orders = array();
        //Log::info("Orders",$res);
        for ($j=0; $j < count($types->data); $j++) {
          $stage[$types->data[$j]->order_stage]['name'] = $types->data[$j]->stage_name;
          $stage[$types->data[$j]->order_stage]['stage'] = $types->data[$j]->order_stage;
          $ct = 0;
          for ($i=0; $i < count($res->data); $i++) {
            if($res->data[$i]->max_stage==$types->data[$j]->order_stage){
              foreach ($res->data[$i] as $key => $value) {
                $stage[$types->data[$j]->order_stage]['data'][$ct][$key] = $value;

                if($res->data[$i]->max_stage!=0 && $res->data[$i]->max_stage!=4){
                  $orders[$pending][$key] = $value;
                }
              }

              $ct++;

              if($res->data[$i]->max_stage!=0 && $res->data[$i]->max_stage!=4){
                $orders[$pending]['stage'] = $value;
                $pending++;
              }
            }
          }
          if($ct==0){
            $stage[$types->data[$j]->order_stage]['data'] = array();
          }
        }

        //Log::info("Orders",$stage);
        if($res->error_code>0 && $res->error_code==404){
          $this->items_count = 0;
          //$this->error = true;
          //$this->error_msg = "No active orders were found at this moment!";
        }else{
          //$this->order_id = $res->data->order_id;
          $this->items_count = $res->data->total;
        }
      } catch (Exception $e) {
        //Cart not found
        $this->error = true;
        $this->error_msg = "Order not found.";
      }

      return View::make('pages.restock.orders',
      array(
          'error' => $this->error,
          'error_msg' => $this->error_msg,
          'cart_id' => $this->order_id,
          'stages' => $stage,
          'orders' => $orders,
          'items_count' => $this->items_count,
          'order_stage_count' => count($types->data),
          'order_count' => $pending,
          'store_id' => $this->store_id,
          'extraJS' => array(
              '/js/restock/restock.js'
          )
      ));
    }
}
