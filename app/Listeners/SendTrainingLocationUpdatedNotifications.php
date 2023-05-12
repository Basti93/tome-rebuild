<?php

namespace App\Listeners;

use App\Events\TrainingLocationUpdated;
use App\Models\Training;
use App\Models\User;
use App\Notifications\TrainingLocationUpdatedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendTrainingLocationUpdatedNotifications implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(TrainingLocationUpdated $event): void
    {
        $athletes = Training::findOrFail($event->training->id)->athletes()->get();
        foreach ($athletes as $athlete) {
            $athlete->notify(new TrainingLocationUpdatedNotification($event->training));
        }
    }
}
