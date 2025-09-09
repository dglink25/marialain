<?php

use Twilio\Rest\Client;

class SmsService {
    protected $client;
    public function __construct() {
        $this->client = new Client(config('services.twilio.sid'), config('services.twilio.token'));
    }
    public function send($to, $body) {
        $from = config('services.twilio.from');
        return $this->client->messages->create($to, ['from'=>$from,'body'=>$body]);
    }
}
