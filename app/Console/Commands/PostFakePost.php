<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Events\PostFakePostAttemptedEvent;

class PostFakePost extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'postfake:post';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a simple POST request to https://atomic.incfile.com/fakepost';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     * 
     * This is an approach using events, listeners and queues for 
     * retrying failed attempts.
     * 
     * It tries the Post once and echoes the response at the terminal.
     * If the request fails, it triggers an event that gets enqueued
     * and is processed asynchronously. It retries ten times.
     * If all the retries fail, the failed job gets recorded in a table.
     *
     * 
     * It's far from ideal, because the POST method is not "idempotent"
     * and we can't be certain if the request failed after or before
     * the server took any actions on it.
     *
     * If we had control over the server upon which we are performing 
     * the POST request, a better aproach would be implementing the 
     * solution suggested at 
     * http://xml.coverpages.org/Prescod-HTTP-ReliableDelivery.html
     * 
     * I used the "database" driver during development, but it should
     * work on any other driver.
     * 
     * @return mixed
     */
    public function handle()
    {
        $url = 'https://atomic.incfile.com/fakepost';
        
        $payload = [
            'payload' => '.',
        ];
        
        echo "Post request sent to {$url}\nWaiting for response...\n\n"; 
        $response = Http::post($url, $payload);

        if ($response->successful()) {

            echo "SUCCESS! :\n - Status: " . $response->status() . "\n - Body: " . $response->body();
        }
        else {

            event(new PostFakePostAttemptedEvent($url, $payload));

            echo "ERROR! :\n - Status: " . $response->status() . "\n - Body: " . $response->body();
            echo "\n\nAn automatic retry job has been enqueued.";
        }
    }
}
