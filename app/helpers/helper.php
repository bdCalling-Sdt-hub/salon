<?php

function ResponseMethod($message,$data)
{
    return response()->json([
        'message' => $message,
        'data' => $data,
    ]);
}
function ResponseMessage($message){
    return response()->json($message);
}

