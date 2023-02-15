<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthApiController extends Controller
{
    protected function registerUser($json){
        $json = json_decode($json,true);
        $validateFields = Validator::make($json, [
            'nick' => 'required',
            'email' => 'required|unique:users',
            'password' => 'required|min:6'
        ]);
        if ($validateFields->fails()) {
            return response()->json([
                'error' => $validateFields->errors()
            ], 401);
        }
        $user = User::create([
            'nick' => $json['nick'],
            'email' => $json['email'],
            'password' => Hash::make($json['password'])
        ]);
        if($user){
            $createdUser = User::where('email',$json['email'])->get();
            return json_encode($createdUser[0]);
        }
        return 'error';
    }
    protected function loginUser($json){
        $json = json_decode($json,true);
        $validateFields = Validator::make($json, [
            'email' => 'required',
            'password' => 'required'
        ]);
        if ($validateFields->fails()) {
            return $validateFields->errors();
        }
        $formFields = array(
            'email' => $json['email'],
            'password' => $json['password']
        );
        if(Auth::attempt($formFields)){
            $user = User::where('email',$formFields['email'])->get();
            return json_encode($user[0]);
        }
        return response()->json([
            'error' => 'error'
        ], 401);
    }
    protected function getAllUsers(){
        $user = User::get('email');
        return json_encode($user);
    }
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
}
