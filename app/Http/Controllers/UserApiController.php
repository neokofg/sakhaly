<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class UserApiController extends Controller
{
    protected function updateUser($json){
        $json = json_decode($json,true);
        $validateFields = Validator::make($json, [
            'email' => 'required',
            'type' => 'required',
            'amount' => 'required|starts_with:+,-'
        ]);
        if ($validateFields->fails()) {
            return response()->json([
                'error' => $validateFields->errors()
            ], 401);
        }
        $user = User::where('email',$json['email'])->get();
        $type = $json['type'];
        foreach($user as $userItem){
            if(Str::startsWith($json['amount'], '+')){
                $plus = explode('+',$json['amount']);
                $amount = $userItem->$type + intval($plus[1]);
            }elseif(Str::startsWith($json['amount'], '-')){
                $minus = explode('-',$json['amount']);
                $amount = $userItem->$type - intval($minus[1]);
            }
            User::where('email',$json['email'])->update([
                $json['type'] => $amount
            ]);
            return response('success',200);
        }
    }
    protected function updateStat($json){
        $json = json_decode($json,true);
        $validateFields = Validator::make($json, [
            'user_id' => 'required|exists:users,id',
            'type' => 'required',
            'amount' => 'required'
        ]);
        if ($validateFields->fails()) {
            return response()->json([
                'error' => $validateFields->errors()
            ], 401);
        }
        $user_id = $json['user_id'];
        $realTime = Carbon::today();;
        $user = User::where('id',$user_id)->get();
        foreach($user as $userItem){
            $decodedStats = json_decode($userItem->stats,true);
            if (array_key_exists($realTime, $decodedStats)) {
                print_r($decodedStats);
            }
            print_r($decodedStats);
        }
    }
}