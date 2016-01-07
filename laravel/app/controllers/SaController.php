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

                $stores = $user->getStores();

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