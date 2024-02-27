<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class TrashController extends Controller
{
    //

    public function allUser()
    {
        $users = User::withTrashed()->paginate(9);
        return response()->json(['Data' => $users]);
    }

    public function trashUser(Request $request)
    {
        $query = User::onlyTrashed();

        // Check if search parameters are provided
        if ($request->has('search')) {
            $search = $request->input('search');
            // Filter by name
            $query
                ->where('name', 'LIKE', "%$search%")
                // Filter by email
                ->orWhere('email', 'LIKE', "%$search%")
                // Filter by date (assuming there's a 'deleted_at' column)
                ->orWhereDate('deleted_at', $search);
        }

        $users = $query->paginate(9);

        if ($users->count() > 0) {
            return ResponseMethod('Trash user List', $users);
        }

        return ResponseMessage('Trash User is empty');
    }

    public function trashRestore($id)
    {
        $users = User::withTrashed()->find($id);

        if (!is_null($users)) {
            $users->restore();
            return response()->json(['Data' => $users]);
        }
        return ResponseMessage('Trash user does not exist');
    }
}
