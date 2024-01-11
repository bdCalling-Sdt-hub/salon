<?php

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
