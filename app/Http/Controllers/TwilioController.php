<?php

namespace App\Http\Controllers;

use Twilio\Rest\Client;
use Illuminate\Http\Request;

class TwilioController extends Controller
{
    // just a test
    public function test()
    {
        // Your Account SID and Auth Token from twilio.com/console
        $sid = env('TWILIO_SID');
        $token = env('TWILIO_TOKEN');
        $client = new Client($sid, $token);

        // Use the client to do fun stuff like send text messages!
        $message = $client->messages 
                  ->create("whatsapp:+6285736676648", // to 
                           array( 
                               "from" => env('TWILIO_WA_FROM'),       
                               "body" => "Hello! This is an editable text message. You are free to change it and write whatever you like." 
                           ) 
                  ); 
        dd($message);
    }

    public function sendMediaUrl()
    {
        // Your Account SID and Auth Token from twilio.com/console
        $sid = env('TWILIO_SID');
        $token = env('TWILIO_TOKEN');
        $client = new Client($sid, $token);

        // Use the client to do fun stuff like send text messages!
        $message = $client->messages 
                  ->create("whatsapp:+6285736676648", // to 
                           array( 
                               "from" => env('TWILIO_WA_FROM'),       
                               "mediaUrl" => ['https://www.twilio.com/assets/icons/twilio-icon-512_maskable.png'],
                               "body" => "ini adalah caption"
                           ) 
                  ); 
        dd($message);
    }
}
