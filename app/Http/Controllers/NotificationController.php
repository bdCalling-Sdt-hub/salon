<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Carbon;
use DB;

class NotificationController extends Controller
{
    public function adminNotification(Request $request)
    {
        $query = DB::table('notifications')
            ->where('type', 'App\Notifications\AdminNotification')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $admin_notification = $query->map(function ($notification) {
            $notification->data = json_decode($notification->data);
            $user_id = $notification->data->user->user_id;
            // Fetch user data based on user_id
            $user = User::find($user_id);
            if ($user) {
                // If user exists, attach user data to notification
                $notification->data->user_details = $user;
            }
            return $notification;
        });

        return response()->json([
            'message' => 'Notification list',
            'data' => $admin_notification,
            'pagination' => [
                'current_page' => $query->currentPage(),
                'first_page_url' => $query->url(1),
                'from' => $query->firstItem(),
                'last_page' => $query->lastPage(),
                'last_page_url' => $query->url($query->lastPage()),
                'links' => [
                    'url' => null,
                    'label' => '&laquo; Previous',
                    'active' => false,
                ],
                'next_page_url' => $query->nextPageUrl(),
                'path' => $query->path(),
                'per_page' => $query->perPage(),
                'prev_page_url' => $query->previousPageUrl(),
                'to' => $query->lastItem(),
                'total' => $query->total(),
            ],
        ]);
    }

    public function account_notification()
    {
        $user = auth()->user();
        return $notifications = $user->notifications;
    }

    public function adminReadAtNotification(Request $request)
    {
        $notification = DB::table('notifications')->find($request->id);
        if ($notification) {
            $notification->read_at = Carbon::now();
            DB::table('notifications')->where('id', $notification->id)->update(['read_at' => $notification->read_at]);
            return response()->json([
                'status' => 'success',
                'message' => 'Notification read successfully.',
            ], 200);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Notification not found',
            ], 404);
        }
    }
}
