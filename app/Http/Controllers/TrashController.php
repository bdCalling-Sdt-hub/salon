<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrashController extends Controller
{
    //

    public function allUser(){
        $users= User::withTrashed()->paginate(9);
        return response()->json(['Data'=>$users]);
    }

    public function trashUser(){
        $users= User::onlyTrashed()->paginate(9);
        if (!is_null($users)){
            return ResponseMethod('Trash user List',$users);
        }
        return ResponseMessage('Trash User is empty');
    }

    public function trashRestore($id){
        $users= User::withTrashed()->find($id);

        if(!is_null($users)){
            $users->restore();
            return response()->json(['Data'=>$users]);
        }
        return ResponseMessage('Trash user does not exist');
    }
}
