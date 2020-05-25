<?php

namespace App\Listeners;

use App\Events\PostFakePostAttemptedEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Http;

class PerformPostFakePostRequestListener implements ShouldQueue
{
    
    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 10;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine the time at which the job should timeout.
     *
     * @return \DateTime
     */
    public function retryUntil()
    {
        return now()->addSeconds(10);
    }

    /**
     * Handle the event.
     *
     * @param  PostFakePostAttemptedEvent  $event
     * @return void
     */
    public function handle(PostFakePostAttemptedEvent $event)
    {
        $response = Http::post($event->url, $event->payload);

        if($response->failed()) {
            fail($response->throw());
        }
    }
}
