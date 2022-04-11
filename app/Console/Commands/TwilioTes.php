<?php

namespace App\Console\Commands;

use Twilio\Rest\Client;
use Illuminate\Console\Command;

class TwilioTes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'twilio';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup my Twilio';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Your Account SID and Auth Token from twilio.com/console
        $sid = env('TWILIO_SID');
        $token = env('TWILIO_TOKEN');
        $client = new Client($sid, $token);

        // Use the client to do fun stuff like send text messages!
        $message = $client->messages 
                  ->create("whatsapp:+628573667664", // to 
                           array( 
                               "from" => "whatsapp:+14155238886",       
                               "body" => "Hello! This is an editable text message. You are free to change it and write whatever you like." 
                           ) 
                  ); 
        dd($message);
    }
}
