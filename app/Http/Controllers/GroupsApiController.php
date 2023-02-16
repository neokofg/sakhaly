<?php

namespace App\Http\Controllers;

use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GroupsApiController extends Controller
{
    protected function newGroup($json){
        $json = json_decode($json,true);
        $validateFields = Validator::make($json, [
            'name' => 'required',
            'teacher_id' => 'required',
        ]);
        if ($validateFields->fails()) {
            return response()->json([
                'error' => $validateFields->errors()
            ], 401);
        }
        $name = $json['name'];
        $teacher_id = $json['teacher_id'];
        $users = '{"users":[]}';
        $group = Group::create([
            'name' => $name,
            'teacher_id' => $teacher_id,
            'users' => $users
        ]);
        if($group){
            $groupInfo = Group::where('id',$group->id)->get();
            return response(json_encode($groupInfo),200);
        }
    }
}
