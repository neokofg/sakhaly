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
            'exercise' => 'required'
        ]);
        $teacher_id = $json['teacher_id'];
        $exercise = $json['exercise'];
        $roomCode = self::quickRandom();
        if ($validateFields->fails()) {
            return response()->json([
                'error' => $validateFields->errors()
            ], 401);
        }
        $room = Room::create([
            'teacher_id' => $teacher_id,
            'exercise' => json_encode($exercise),
            'room_code' => $roomCode,
            'status' => 'wait'
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
        return response(json_encode($roomUpdate[0]),200);
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
            $decodedUsers = json_decode($roomItem->users,true);
            if(in_array(intval($user_id), $decodedUsers['users'])){
                return response()->json([
                    'error' => 'User already exists!'
                ], 401);
            }else{
                array_push($decodedUsers['users'],intval($user_id));
            }
        }
        Room::where('room_code',$json['room_code'])->update([
            'users' => json_encode($decodedUsers)
        ]);
    }
}
