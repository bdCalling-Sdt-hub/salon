<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon;
use DB;

class NotificationController extends Controller
{
    public function adminNotification()
    {
        $auth_user = auth()->user()->id;
        // $provider = Provider::where('user_id', $auth_user)->first();

        $notifications = DB::table('notifications')
            ->where('type', 'App\Notifications\AdminNotification')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $notificationsForProvider4 = [];

        foreach ($notifications as $notification) {
            $data = json_decode($notification->data);

            $notificationData = [
                'id' => $notification->id,
                'read_at' => $notification->read_at,
                'type' => $notification->type,
                'data' => $data,
            ];
            $notificationsForProvider4[] = $notificationData;
            // }
        }

        return response()->json([
            'status' => 'success',
            'notification' => $notificationData,
            'admin_notification' => $this->account_notification(),
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
