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
            'teacher_id' => 'required|unique:groups',
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
            return response(json_encode($groupInfo[0],JSON_UNESCAPED_UNICODE),200);
        }
    }
    protected function addUserToGroup($json){
        $json = json_decode($json,true);
        $validateFields = Validator::make($json, [
            'user_id' => 'required|exists:users,id',
            'group_id' => 'required|exists:groups,id'
        ]);
        if ($validateFields->fails()) {
            return response()->json([
                'error' => $validateFields->errors()
            ], 401);
        }
        $user_id = $json['user_id'];
        $group_id = $json['group_id'];
        $group = Group::where('id',$group_id)->get();
        foreach($group as $groupItem){
            $decodedUsers = json_decode($groupItem->users,true);
            if(in_array(intval($user_id), $decodedUsers['users'])){
                return response()->json([
                    'error' => 'User already exists!'
                ], 401);
            }else{
                array_push($decodedUsers['users'],intval($user_id));
            }
        }
        Group::where('id',$group_id)->update([
            'users' => json_encode($decodedUsers,JSON_UNESCAPED_UNICODE)
        ]);
        $groupUpdated = Group::where('id',$group_id)->get();
        return response(json_encode($groupUpdated[0],JSON_UNESCAPED_UNICODE),200);
    }
    protected function deleteUserFromGroup($json){
        $json = json_decode($json,true);
        $validateFields = Validator::make($json, [
            'user_id' => 'required|exists:users,id',
            'group_id' => 'required|exists:groups,id'
        ]);
        if ($validateFields->fails()) {
            return response()->json([
                'error' => $validateFields->errors()
            ], 401);
        }
        $user_id = $json['user_id'];
        $group_id = $json['group_id'];
        $group = Group::where('id',$group_id)->get();
        foreach($group as $groupItem){
            $decodedUsers = json_decode($groupItem->users,true);
            if(in_array(intval($user_id), $decodedUsers['users'])){
                $key = array_search($user_id, $decodedUsers['users']);
                unset($decodedUsers['users'][$key]);
            }else{
                return response()->json([
                    'error' => 'User didn"t exists!'
                ], 401);
            }
        }
        Group::where('id',$group_id)->update([
            'users' => json_encode($decodedUsers,JSON_UNESCAPED_UNICODE)
        ]);
        $groupUpdated = Group::where('id',$group_id)->get();
        return response(json_encode($groupUpdated[0],JSON_UNESCAPED_UNICODE),200);
    }
    protected function getGroups(){
        $user = Group::get();
        return response(json_encode($user,JSON_UNESCAPED_UNICODE),200);
    }
}
