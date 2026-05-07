<?php

namespace App\Events;

use App\Models\Contract;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ContractAssigned
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var Contract
     */
    public Contract $contract;

    /**
     * @var User|null
     */
    public ?User $user;

    /**
     * Create a new event instance.
     */
    public function __construct(Contract $contract, ?User $user = null)
    {
        $this->contract = $contract;
        $this->user = $user;
    }

    public function contract(): Contract
    {
        return $this->contract;
    }

    public function user(): ?User
    {
        return $this->user;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}
