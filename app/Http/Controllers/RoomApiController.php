<?php

namespace App\Http\Controllers;

use App\Models\Room;
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
            'exercise' => json_encode($exercise,JSON_UNESCAPED_UNICODE ),
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
        return response(json_encode($roomUpdate[0],JSON_UNESCAPED_UNICODE ),200);
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
                $i = 0;
                $answers = array();
                while($i == $answersCount){
                    $answers = array_merge($answers,[$i => 0]);
                    $i++;
                }
                print_r($answers);
                $userArray = array(
                    $user_id => array(
                        'answers' => $answers,
                        'balls' => 0
                    )
                );
                array_push($decodedUsers['users'],$userArray);
            }
        }
        Room::where('room_code',$json['room_code'])->update([
            'users' => json_encode($decodedUsers,JSON_UNESCAPED_UNICODE )
        ]);
        $roomUpdate = Room::where('room_code',$json['room_code'])->get();
        return response(json_encode($roomUpdate[0],JSON_UNESCAPED_UNICODE ),200);
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
            if(in_array(intval($user_id), $decodedUsers['users'])){
                $key = array_search($user_id, $decodedUsers['users']);
                unset($decodedUsers['users'][$key]);
            }else{
                return response()->json([
                    'error' => 'User already exists!'
                ], 401);
            }
        }
        Room::where('room_code',$json['room_code'])->update([
            'users' => json_encode($decodedUsers,JSON_UNESCAPED_UNICODE )
        ]);
        $roomUpdate = Room::where('room_code',$json['room_code'])->get();
        return response(json_encode($roomUpdate[0],JSON_UNESCAPED_UNICODE ),200);
    }
}
