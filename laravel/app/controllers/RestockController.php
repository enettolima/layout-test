<?php

class RestockController extends BaseController
{
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
        Log::info("Store ID on construct ".$this->store_id);
        $this->getCartStatus();
    }

    public function getIndex()
    {
      Log::info("Store ID on index ".$this->store_id);
      return Redirect::to('/restock/browse');
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
                    '/js/restock/restock.js'
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
        $api = new EBTAPI;
        $res = $api->get("/restock/cart/".$this->store_id."/product");
        Log::info("Status API Call",array('response' => $res));
        /*if($res->error_code>0 && $res->error_code==404){
          $this->items_count = 0;
          //$this->error = true;
          //$this->error_msg = "No active orders were found at this moment!";
        }else{
          //$this->order_id = $res->data->order_id;
          $this->items_count = $res->data->total;
        }*/
        //$response = json_decode($res->data, true);
        $response = $res->data;
      } catch (Exception $e) {
        //Cart not found
        $this->error = true;
        $this->error_msg = "Cart is currently unavailable.";
      }


      /*foreach ($response as $prod) {
        echo $prod->description."<br>";
      }*/

      /*

      <!---->*/
      return View::make('pages.restock.carts',
        array(
          'error' => $this->error,
          'error_msg' => $this->error_msg,
          'cart_id' => $this->order_id,
          'items_count' => $this->items_count,
          'store_id' => $this->store_id,
          'response' => $response,
          'count_data' => count($response),
          'extraJS' => array(
            '/js/restock/restock.js'
          )
        )
      );
    }

    public function getOrders()
    {
      return View::make('pages.restock.orders',
      array(
          'error' => $this->error,
          'error_msg' => $this->error_msg,
          'cart_id' => $this->order_id,
          'items_count' => $this->items_count,
          'store_id' => $this->store_id,
          'extraJS' => array(
              '/js/restock/restock.js'
          )
      ));
    }
}
