<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

use App\Model\User;

class AssignDefaultPlan
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

   
    public $user,$platform, $plan_id, $transaction_id, $product_id ;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(User $user, $data)
    {
        $this->user = $user;
        $this->platform = $data['platform'];
        $this->product_id = $data['product_id'];
        $this->plan_id = $data['plan_id'];
        $this->transaction_id = $data['transaction_id'];
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
