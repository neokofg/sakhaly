<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RoomApiController extends Controller
{
    protected function createRoom($json){
        $json = json_decode($json,true);
        $validateFields = Validator::make($json, [
            'teacher_id' => 'required|exists:users,id',
            'exercise' => 'required',
            'answers' => 'required'
        ]);
        $teacher_id = $json['teacher_id'];
        $exercise = $json['exercise'];
        $answers = $json['answers'];
        $roomCode = self::quickRandom();
        if ($validateFields->fails()) {
            return response()->json([
                'error' => $validateFields->errors()
            ], 401);
        }
        $room = Room::create([
            'teacher_id' => $teacher_id,
            'exercise' => json_encode($exercise,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES ),
            'room_code' => $roomCode,
            'status' => 'wait',
            'answers' => $answers
        ]);
        $roomUpdate = Room::where('id', $room->id)->get();
        foreach($roomUpdate as $roomItem){
            while($roomCode == $roomItem->room_code){
                $roomCode = self::quickRandom();
                Room::where('id',$room->id)->update([
                   'room_code' => $roomCode
                ]);
            }
        }
        $roomUpdate = Room::where('id', $room->id)->get();
        return response(json_encode($roomUpdate[0],JSON_UNESCAPED_UNICODE |JSON_UNESCAPED_SLASHES),200);
    }
    public static function quickRandom($length = 5)
    {
        $pool = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';

        return substr(str_shuffle(str_repeat($pool, 5)), 0, $length);
    }
    protected function joinRoom($json){
        $json = json_decode($json,true);
        $validateFields = Validator::make($json, [
            'room_code' => 'required|exists:rooms',
            'user_id' => 'required|exists:users,id'
        ]);
        if ($validateFields->fails()) {
            return response()->json([
                'error' => $validateFields->errors()
            ], 401);
        }
        $room = Room::where('room_code',$json['room_code'])->get();
        $user_id = $json['user_id'];
        foreach($room as $roomItem){
            if($roomItem->status !== 'wait'){
                return response()->json([
                    'error' => 'Round has already started!'
                ], 401);
            }
            $answersArray = $roomItem->answers;
            $answersArray = str_replace('[','',$answersArray);
            $answersArray = str_replace(']','',$answersArray);
            $answersArray = explode(',',$answersArray);
            $decodedUsers = json_decode($roomItem->users,true);
            $answersCount = count($answersArray);
            if(in_array(intval($user_id), $decodedUsers['users'])){
                return response()->json([
                    'error' => 'User already exists!'
                ], 401);
            }else{
                if($roomItem->users == '{"users":{}}'){
                    $i = 1;
                    $answers = array();
                    while($i <= $answersCount){
                        $answers += [strval($i) => 0];
                        $i++;
                    }
                    $user = User::where('id',$user_id)->get();
                    foreach($user as $userItem){
                        $decodedUsers['users'] = array(
                            strval($user_id) => array(
                                'answers' => $answers,
                                'balls' => 0,
                                'name' => $userItem->nick
                            )
                        );
                    }
                    Room::where('room_code',$json['room_code'])->update([
                        'users' => json_encode($decodedUsers,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES )
                    ]);
                    $roomUpdate = Room::where('room_code',$json['room_code'])->get();
                    return response(json_encode($roomUpdate[0],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES ),200);
                }else{
                    if(array_key_exists(strval($user_id), $decodedUsers['users'])){
                        return response()->json([
                            'error' => 'User already exists!'
                        ], 401);
                    }
                    $i = 1;
                    $answers = array();
                    while($i <= $answersCount){
                        $answers += [strval($i) => 0];
                        $i++;
                    }
                    $user = User::where('id',$user_id)->get();
                    foreach($user as $userItem){
                        $userArray = array(
                            strval($user_id) => array(
                                'answers' => $answers,
                                'balls' => 0,
                                'name' => $userItem->nick
                            )
                        );
                    }
                    $result = $decodedUsers['users'] + $userArray;
                    $newArray = '{"users":{}}';
                    $newArray = json_decode($newArray,true);
                    array_push($newArray['users'],$result);
                    $newArray = json_encode($newArray,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
                    $newArray = str_replace('[','',$newArray);
                    $newArray = str_replace(']','',$newArray);
                    Room::where('room_code',$json['room_code'])->update([
                        'users' => $newArray
                    ]);
                    $roomUpdate = Room::where('room_code',$json['room_code'])->get();
                    return response(json_encode($roomUpdate[0],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES ),200);
                }
            }
        }
    }
    protected function leaveRoom($json){
        $json = json_decode($json,true);
        $validateFields = Validator::make($json, [
            'room_code' => 'required|exists:rooms',
            'user_id' => 'required|exists:users,id'
        ]);
        if ($validateFields->fails()) {
            return response()->json([
                'error' => $validateFields->errors()
            ], 401);
        }
        $room = Room::where('room_code',$json['room_code'])->get();
        $user_id = $json['user_id'];
        foreach($room as $roomItem){
            $decodedUsers = json_decode($roomItem->users,true);
            if(array_key_exists(strval($user_id), $decodedUsers['users'])){
                unset($decodedUsers['users'][strval($user_id)]);
            }else{
                return response()->json([
                    'error' => 'User doesn"t exists!'
                ], 401);
            }
        }
        Room::where('room_code',$json['room_code'])->update([
            'users' => json_encode($decodedUsers,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES )
        ]);
        $roomUpdate = Room::where('room_code',$json['room_code'])->get();
        foreach($roomUpdate as $roomUpdateItem){
            if($roomUpdateItem->users == '{"users":[]}'){
                Room::where('room_code',$json['room_code'])->update([
                    'users' => '{"users":{}}'
                ]);
            }
        }
        $roomUpdate = Room::where('room_code',$json['room_code'])->get();
        return response(json_encode($roomUpdate[0],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),200);
    }
    function Test(){
        $json_massive = '{"users":[{"1":{"answers":{"1":0,"2":0,"3":0,"4":0,"5":0},"balls":0}}]}';
        $json_massive = json_decode($json_massive, true);
        $json_massive = $json_massive['users'];
        $json_massive = $json_massive[0];
        print_r($json_massive);
        if(array_key_exists(strval(1), $json_massive)){
            print_r('Нашел!');
        }else {
            print_r('!Нашел');
        }
    }
    protected function getRoom($json){
        $json = json_decode($json,true);
        $validateFields = Validator::make($json, [
            'room_code' => 'required|exists:rooms',
        ]);
        if ($validateFields->fails()) {
            return response()->json([
                'error' => $validateFields->errors()
            ], 401);
        }
        $roomCode = $json['room_code'];
        $room = Room::where('room_code',$roomCode)->get();
        return response(json_encode($room[0],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),200);
    }
    protected function startRoom($json){
        $json = json_decode($json,true);
        $validateFields = Validator::make($json, [
            'room_code' => 'required|exists:rooms',
        ]);
        if ($validateFields->fails()) {
            return response()->json([
                'error' => $validateFields->errors()
            ], 401);
        }
        $roomCode = $json['room_code'];
        Room::where('room_code',$roomCode)->update([
            'status' => 'started'
        ]);
        $room = Room::where('room_code',$roomCode)->get();
        return response(json_encode($room[0],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),200);
    }
}
