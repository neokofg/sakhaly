<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthApiController extends Controller
{
    protected function registerUser($json){
        $json = json_decode($json,true);
        $validateFields = Validator::make($json, [
            'nick' => 'required',
            'email' => 'required|unique:users',
            'password' => 'required|min:6'
        ]);
        $user = User::create([
            'nick' => $json['nick'],
            'email' => $json['email'],
            'password' => Hash::make($json['password'])
        ]);
        if($user){
            return 'success!';
        }
        return 'Произошла ошибка!';
    }
    protected function loginUser($json){
        $json = json_decode($json,true);
        $validateFields = Validator::make($json, [
            'email' => 'required',
            'password' => 'required'
        ]);
        $formFields = array(
            'email' => $json['email'],
            'password' => $json['password']
        );
        if(Auth::attempt($formFields)){
            $user = User::where('email',$formFields['email'])->get();
            return json_encode($user);
        }
        return 'Не удалось авторизироваться!';
    }
}
