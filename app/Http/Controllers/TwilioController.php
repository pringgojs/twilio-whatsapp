<?php

namespace App\Http\Controllers;

use Exception;
use Mimey\MimeTypes;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Twilio\Exceptions\RestException;
use GuzzleHttp\Client as GuzzleClient;
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

    /** 
     * [POST] Send text message
     * @param int number
     * @param string message
     * 
     * @return string 
    */
    public function sendMessageText(Request $request)
    {
        try {
            $response = $this->responseMessageText($request->number, $request->message);
            return response($response['message'], $response['success'] ? 200 : 422)->header('Content-Type', 'text/plain');
        } catch (Exception $e) {
            return response('Error', 422)->header('Content-Type', 'text/plain');
        }
    }

    /** 
     * [POST] Send text message
     * 
     * @param int number
     * @param string message
     * @param string url media http://*./path-to-media/*.(.png, .jpg, etc.)
     * 
     * @return string 
    */
    public function sendMessageMedia(Request $request)
    {
        try {
            $response = $this->responseMessageMedia($request->number, $request->message, $request->url);
            return response($response['message'], $response['success'] ? 200 : 422)->header('Content-Type', 'text/plain');
        } catch (Exception $e) {
            return response('Error', 422)->header('Content-Type', 'text/plain');
        }
    }

    /** 
     * Twilio message text
     * @param int $to Whatsapp number
     * @param string $message Message
     * 
     * @return Array 
    */
    public function responseMessageText($to = null, $message)
    {
        $sid = env('TWILIO_SID');
        $token = env('TWILIO_TOKEN');
        $client = new TwilioClient($sid, $token);

        try {
            $twilio = $client->messages 
                ->create("whatsapp:+".$to, // to 
                        array( 
                            "from" => env('TWILIO_WA_FROM'),       
                            "body" => $message 
                        ) 
                ); 
            return ['success' => true, 'message' => 'Success'];

        } catch (RestException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /** 
     * Twilio message media
     * @param int $to Whatsapp number
     * @param string $message Message
     * @param string url media http://*./path-to-media/*.(.png, .jpg, etc.)
     * 
     * @return Array 
    */
    public function responseMessageMedia($to = null, $message = null, $url = null)
    {
        $sid = env('TWILIO_SID');
        $token = env('TWILIO_TOKEN');
        $client = new TwilioClient($sid, $token);

        try {
            $message = $client->messages 
                ->create('whatsapp:+'. $to, // to 
                    array( 
                        'from' => env('TWILIO_WA_FROM'),       
                        'mediaUrl' => [$url],
                        'body' => $message
                    ) 
                ); 

            return ['success' => true, 'message' => 'Success'];
        } catch (RestException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }

    }

    /** 
     * Catch all webhook from Twilio
     *  
     * */
    public function webhook(Request $request)
    {
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

            $mimes = new MimeTypes;
            $extension = $mimes->getExtension($request['MediaContentType0']);

            $base_64 = base64_encode(file_get_contents($request['MediaUrl0']));
            $data = [
                'mimetype' => $request['MediaContentType0'],
                'data' => $base_64,
                'filename' => Str::random(10).".".$extension
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

        $params = ['number' => $from.'@c.us', 'message' => $data];
        $response_text = self::curl($params);

        // return 
        info("Response CMS:" . $response_text);

        self::responseMessageText($from, $response_text);
    }

    /** curl to CMS sipandu */
    public function curl($data)
    {
        $url = env('URL_CMS_SIPANDU');

        info($url);
        try {
            $client = new GuzzleClient([
                'headers' => [ 'Content-Type' => 'application/json' ]
            ]);
            $response = $client->request('POST', $url, [
                'json' => $data,
                'verify' => false
            ]);
    
            info("Status curl: ". $response->getStatusCode());
            return $response->getBody();

        } catch (Exception $e) {
            info('CURL ERROR: '. $url);
            info($e);
            return "System error";
        }
    }
}
