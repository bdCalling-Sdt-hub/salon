<?php
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
?>
