<?php

namespace App\Http\Controllers\API;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    public function return400($errors = false) {
        return response([
            'result'    => false,
            'error'     => $errors ? (is_array($errors)|| is_object($errors)) ? $errors : ['common' =>$errors] :  ['common' => config('app.internal_error')]
        ], 400);
    }

    public function returnSuccess($data) {
        return [
            'result' => true,
            'data' => $data
        ];
    }
}


