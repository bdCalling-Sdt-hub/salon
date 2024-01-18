<?php
use App\Events\SendNotification;
use App\Notifications\UserNotification;

function ResponseMethod($status, $message)
{
    return response()->json([
        'status' => $status,
        'message' => $message,
    ], 200);
}

function ResponseErrorMessage($status, $message)
{
    return response()->json([
        'status' => $status,
        'message' => $message,
    ], 404);
}

// =====================NOTIFICATION==================//

function sendNotification($message, $data)
{
    try {
        event(new SendNotification($message, $data));
        \Notification::send($data, new UserNotification($data));
        return response()->json(['success' => true, 'msg' => 'Notification Added'], 200);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'msg' => $e->getMessage()]);
    }
}

// ======================TOKENT=============//
