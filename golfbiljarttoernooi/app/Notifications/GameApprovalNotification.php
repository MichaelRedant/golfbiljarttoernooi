<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Game;

class GameApprovalNotification extends Notification
{
    use Queueable;

    protected $game;

    public function __construct(Game $game)
    {
        $this->game = $game;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->line('Een gespeelde wedstrijd wacht op uw goedkeuring.')
                    ->action('Approve Match', route('games.approve', $this->game->id))
                    ->line('Bedankt om onze app te gebruiken!');
    }
}
