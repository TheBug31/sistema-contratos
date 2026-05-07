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

class ContractReassigned
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var Contract
     */
    public Contract $contract;

    public int $oldAdvisorId;
    public int $newAdvisorId;

    /**
     * @var User|null
     */
    public ?User $user;

    /**
     * Create a new event instance.
     */
    public function __construct(Contract $contract, int $oldAdvisorId, int $newAdvisorId, ?User $user = null)
    {
        $this->contract = $contract;
        $this->oldAdvisorId = $oldAdvisorId;
        $this->newAdvisorId = $newAdvisorId;
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
