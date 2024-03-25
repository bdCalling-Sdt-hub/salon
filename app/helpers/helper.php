<?php
use App\Events\SendNotification;
use App\Notifications\AdminNotification;
use App\Notifications\SalonNotification;
use App\Notifications\UserNotification;
use Illuminate\Support\Facades\Notification;

function ResponseMethod($message, $data)
{
    return response()->json([
        'message' => $message,
        'data' => $data,
    ]);
}

function ResponseErroMethod($message, $data)
{
    return response()->json([
        'message' => $message,
        'data' => $data,
    ]);
}

function ResponseMessage($message)
{
    return response()->json([
        'message' => $message,
    ]);
}

function sendNotification($message = null, $description = null, $data = null, $payment = null)
{
    try {
        event(new SendNotification($message, $data));
        Notification::send($data, new UserNotification($message, $description, $data));
        return response()->json([
            'success' => true,
            'msg' => 'Notification Added',
            'data' => $data,
        ]);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'msg' => $e->getMessage()]);
    }
}

function providerNotification($message = null, $description = null, $data = null, $payment = null)
{
    try {
        event(new SendNotification($message, $data));
        Notification::send($data, new SalonNotification($message, $description, $data));
        return response()->json([
            'success' => true,
            'msg' => 'Notification Added',
            'data' => $data,
        ]);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'msg' => $e->getMessage()]);
    }
}

function adminNotification($message = null, $description = null, $data = null, $payment = null)
{
    try {
        event(new SendNotification($message, $data));
        Notification::send($data, new AdminNotification($message, $description, $data));
        return response()->json([
            'success' => true,
            'msg' => 'Notification Added',
            'data' => $data,
        ]);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'msg' => $e->getMessage()]);
    }
}
function ResponseErrorMessage($message){
    return response()->json([
        'message' => $message,
    ]);
}
?>
