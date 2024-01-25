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

function ResponseErroMethod($status, $message)
{
    return response()->json([
        'status' => $status,
        'message' => $message,
    ]);
}

function ResponseMessage($message)
{
    return response()->json([
        'message' => $message,
    ]);
}

// =====================NOTIFICATION==================//

// function sendNotification($message, $data)
// {
//     try {
//         event(new SendNotification($message, $data));
//         \Notification::send($data, new UserNotification($data));
//         return response()->json(['success' => true, 'msg' => 'Notification Added'], 200);
//     } catch (\Exception $e) {
//         return response()->json(['success' => false, 'msg' => $e->getMessage()]);
//     }
// }

function sendNotification($message, $data = null, $payment = null)
{
    try {
        event(new SendNotification($message, $data));

        return response()->json([
            'success' => true,
            'msg' => 'Notification Added',
            'data' => $data,
        ]);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'msg' => $e->getMessage()]);
    }
}
?>
