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
        $teacher_id = $json['teacher_id'];
        $exercise = $json['exercise'];
        $roomCode = self::quickRandom();
        $status = 'wait';
        if ($validateFields->fails()) {
            return response()->json([
                'error' => $validateFields->errors()
            ], 401);
        }
        $room = Room::create([
            'teacher_id' => $teacher_id,
            'exercise' => $exercise,
            'room_code' => $roomCode,
            'status' => $status
        ]);
        $roomUpdate = Room::where('id', $room->id)->get();
        return response(json_encode($roomUpdate[0]),200);
    }
    public static function quickRandom($length = 5)
    {
        $pool = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';

        return substr(str_shuffle(str_repeat($pool, 5)), 0, $length);
    }
}
