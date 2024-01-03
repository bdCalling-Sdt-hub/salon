<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;

class TrashController extends Controller
{
    public function all_user_with_trash(){
        $users= User::withTrashed()->get();
        return response()->json(['Data'=>$users]);
    }

    public function trash_user_list(){
        $users= User::onlyTrashed()->get();
        return response()->json(['Data'=>$users]);
    }

    public function trash_restore($id){
        $users= User::withTrashed()->find($id);

        if(!is_null($users)){
            $users->restore();
            return response()->json(['Data'=>$users]);
        }
    }

    public function trash_permanent_delete($id){
        $users= User::withTrashed()->find($id);

        if(!is_null($users)){
            $users->forceDelete();
            return response()->json(['Data'=>$users]);
        
        }
    }
}
