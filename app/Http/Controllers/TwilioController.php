<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Twilio\Rest\Client as TwilioClient;

class TwilioController extends Controller
{
    // just a test
    public function test()
    {
        // Your Account SID and Auth Token from twilio.com/console
        $sid = env('TWILIO_SID');
        $token = env('TWILIO_TOKEN');
        $client = new TwilioClient($sid, $token);

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

    /** send media from url */
    public function sendMediaUrl()
    {
        // Your Account SID and Auth Token from twilio.com/console
        $sid = env('TWILIO_SID');
        $token = env('TWILIO_TOKEN');
        $client = new TwilioClient($sid, $token);

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

    /** catch all webhook */
    public function webhook(Request $request)
    {
        info("webhook twilio");
        info($request->all());

        $from = explode('+', $request['From']);
        $from = isset($from[1]) ? $from[1] : null;
        $data = null;

        if (!$from) return false;

        /** is media */
        if (isset($request['MediaContentType0']) && isset($request['MediaUrl0'])) {
            /** format ke CMS */

            // [
            //     'mimetype': '',
            //     'data': ''
            //     'filename': ''
            // ];

            $base_64 = base64_encode(file_get_contents($request['MediaUrl0']));
            $data = [
                'mimetype' => $request['MediaContentType0'],
                'data' => $base_64,
                'filename' => Str::random(10).".jpeg"
            ];

            info("webhook media");
            info($data);

        }

        /** is location */
        if (isset($request['Latitude'])) {
            /** format ke CMS */
            // [
            //     latitude: -7.870908,
            //     longitude: 111.488152,
            //     description: 'Kantor Pengadilan Agama Ponorogo\nJl.IR.Juanda No.25, Ponorogo, Jawa Timur'
            // ]

            $data = [
                'latitude' => $request['Latitude'],
                'longitude' => $request['Longitude'],
                'description' => $request['Address'],
            ];

            
            info("webhook location");
            info($data);
        }

        /** is Text */
        if ($request['NumMedia'] == 0 && $request['Body'] != null) {
            /** format CMS */
            $data = $request['Body'];

            info("webhook text");
            info($data);

        }

        $params = ['from' => $from.'@c.us', 'message' => $data];
        $response_text = self::curl($params);
        info("Response CMS:" . $response_text);
    }

    /** curl to CMS sipandu */
    public function curl($data)
    {
        $url = env('URL_CMS_SIPANDU');
        $client = new GuzzleClient([
            'headers' => [ 'Content-Type' => 'application/json' ]
        ]);
        $response = $client->request('POST', $endpoint, [
            'json' => $data,
            'verify' => false
        ]);

        info("Status curl: ". $response->getStatusCode());
        return $response->getBody();
    }
}
