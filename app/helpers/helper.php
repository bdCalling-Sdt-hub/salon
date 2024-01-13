<?php
use App\Events\SendNotification;
use App\Notifications\UserNotification;

function ResponseMethod($status, $message)
{
    return response()->json([
        'status' => $status,
        'message' => $message,
    ]);
}

function ResponseErrorMessage($status, $message)
{
    return response()->json([
        'status' => $status,
        'message' => $message,
    ]);
}

// =====================NOTIFICATION==================//

function sendNotification($message, $data)
{
    try {
        event(new SendNotification($message, $data));
        \Notification::send($data, new UserNotification($data));
        return response()->json(['success' => true, 'msg' => 'Notification Added']);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'msg' => $e->getMessage()]);
    }
}
