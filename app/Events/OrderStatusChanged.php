<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $order;

    /**
     * Create a new event instance.
     */
    public function __construct(Order $order)
    {
        $this->order = $order->load('items.menu', 'table');
    }

    /**
     * Get the channels the event should broadcast on.
     * Channel: restaurants.{restaurant_id}
     * Everyone in the restaurant (Kitchen, Cashier, Display) listens to this.
     */
    public function broadcastOn(): array
    {
        return [
            new channel('restaurants.' . $this->order->restaurant_id),
        ];
    }

    public function broadcastAs()
    {
        return 'order.status.updated';
    }
}
