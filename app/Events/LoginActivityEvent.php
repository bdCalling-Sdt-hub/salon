<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

//class LoginActivityEvent
//{
//    use Dispatchable, InteractsWithSockets, SerializesModels;
//
//    public function __construct()
//    {
//        //
//    }
//
//    public function broadcastOn(): array
//    {
//        return [
//            new PrivateChannel('channel-name'),
//        ];
//    }
//}

class LoginActivityEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;
    public $browser;
    public $deviceName;
    public $location;
    public $loginTime;
    public $status;

    public function __construct(User $user, $browser, $deviceName, $location, $loginTime, $status)
    {
        $this->user = $user;
        $this->browser = $browser;
        $this->deviceName = $deviceName;
        $this->location = $location;
        $this->loginTime = $loginTime;
        $this->status = $status;
    }
}

