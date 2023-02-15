<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthApiController extends Controller
{
    protected function registerUser($json){
        $json = json_decode($json);
        $validateFields = $json->validate([
            'nick' => 'required',
            'email' => 'required|unique:users',
            'password' => 'required|min:6'
        ]);
        $user = User::create([
            'name' => $validateFields['name'],
            'email' => $validateFields['email'],
            'password' => Hash::make($validateFields['password'])
        ]);
        if($user){
            return 'success!';
        }
        return 'Произошла ошибка!';
    }
}
