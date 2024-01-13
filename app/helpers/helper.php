<?php
use App\Events\SendNotification;
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

function sendNotification($message, $data=null, $payment = null)
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
