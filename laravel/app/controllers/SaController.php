<?php

class SaController extends BaseController
{
    public function getStores()
    {
        $response = array();

        try{

            $u = trim(Request::segment(3));

            if (preg_match('/^(\d\d\d).*$/', $u)) {

                $user = User::where('username', $u)->firstOrFail();

                $stores = array();

                $sr = StoresResolver::getInstance();

                foreach ($user->getStores() as $store) {

                    $stores[$store] = $sr->getStore($store)->store_name;
                }

                $response['data'] = $stores;
            } else {
                throw new Exception("Invalid user form.");
            }

        }catch(Exception $e){

            $response['errors'][] = $e->getMessage();
        }

        return Response::json($response);

    }
}