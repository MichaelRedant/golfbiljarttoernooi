<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ScoreUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $score;

    public function __construct($score)
    {
        $this->score = $score;
        Log::info('ScoreUpdated event constructed with data: ', $score);
    }

    public function broadcastOn()
    {
        Log::info('Broadcasting on channel: match.' . $this->score['match_id']);
        return new Channel('match.' . $this->score['match_id']);
    }
}
