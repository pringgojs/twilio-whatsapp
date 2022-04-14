## Twilio Laravel APP

Twilio Laravel APP sebuah API Whatsapp adapter untuk Twilio.

## Fitur

Twilio App Laravel 9.x digunakan untuk API Whatsaapp secara resmi dengan Twilio.
Fitur:

-   Webhook untuk handle incoming dan outcoming message. Path: [POST] `api/webhook`. Support `Json` request.
-   Kirim pesan text. Path: `api/v1/text-message`
-   Kirim pesan text dengan url media. Path: `api/v1/send-media-url`

## Setup

Pastikan Twilio console sudah ready. Selanjutnya config di APP dengan edit file `.env`

`TWILIO_SID=`
`TWILIO_TOKEN=`
`TWILIO_WA_FROM=whatsapp:+14155238886`
