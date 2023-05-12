<?php

namespace App\Notifications;

use App\Models\Training;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Channels\MailChannel;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use NotificationChannels\PusherPushNotifications\PusherChannel;
use NotificationChannels\PusherPushNotifications\PusherMessage;

class TrainingLocationUpdatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public Training $training)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $notifications = User::find($notifiable->id)->notificationSettings()->where('notification_id', 1)->get();
        $channels = [];
        foreach ($notifications as $notification) {
            if ($notification->pivot->type == 'email') {
                Log::debug('User '.$notifiable->id.' has email channel');
                array_push($channels, MailChannel::class);
            }
            if ($notification->pivot->type == 'push') {
                Log::debug('User '.$notifiable->id.' has push channel');
                array_push($channels, PusherChannel::class);
            }
        }
        return $channels;
    }

    /**
     * Determine if the notification should be sent.
     */
    public function shouldSend(object $notifiable, string $channel): bool
    {
        //only send wenn training is in the future and in the next 7 days
        //and if the user is an athlete of the training
        return
            $this->training->date_start > now()
            && $this->training->date_start < now()->addDays(7)
            && $this->training->athletes()->where('user_id', $notifiable->id)->exists();
    }

    public function toPushNotification(object $notifiable): PusherMessage
    {
        return PusherMessage::create()
            ->web()
            ->link(env('APP_URL').'/#/training/'.$this->training->id)
            ->title('Trainingsort Aktualisiert')
            ->body('Training '.$this->training->date_start->format('D \d\e\n d.m.Y H:i').' Uhr. Neuer Trainingsort: '.$this->training->location->name);
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('Trainingsort aktualisiert für '.$this->training->date_start->format('D \d\e\n d.m.Y H:i').' Uhr   ')
                    ->line('Trainingsort aktualisiert für dein Training am '.$this->training->date_start->format('D \d\e\n d.m.Y H:i').' Uhr')
                    ->line('Neuer Trainingsort: '.$this->training->location->name)
                    ->action('Hier klicken für Details', url(env('APP_URL').'/#/training/'.$this->training->id));
    }

}
