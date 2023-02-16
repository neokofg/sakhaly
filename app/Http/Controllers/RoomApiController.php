<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RoomApiController extends Controller
{
    protected function createRoom($json){
        $json = json_decode($json,true);
        $validateFields = Validator::make($json, [
            'teacher_id' => 'required|exists:users,id',
            'exercise' => 'required'
        ]);
        if ($validateFields->fails()) {
            return response()->json([
                'error' => $validateFields->errors()
            ], 401);
        }
    }
}
