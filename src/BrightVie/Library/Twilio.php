<?php
namespace BrightVie\Library;

/**
 *
 * ```
 *
 * $libTwilio = new BrightVie\Library\Twilio($sid, $token);
 *
 * $libTwilio->sendSMS(''+819012345678, '送信メッセジ');
 */
class Twilio {

    // Twilio Rest Client
    private $client;

    private $fromPhoneNumber;


    function __construct($sid, $token) {

        $this->client = new \Twilio\Rest\Client($sid, $token);
    }

    /**
     * 送信元の電話番号をセットする
     */
    public function setFromPhoneNumber($fromPhoneNumber) {
        $this->fromPhoneNumber = $fromPhoneNumber;
    }

    /**
     * 1つの電話番号に対して同じSMSを送る
     */
    public function sendSMS($sendNumber, $sendMessage) {
        $res = $this->client->messages->create(
            $sendNumber,
            array(
                'from' => $this->fromPhoneNumber,
                'body' => $sendMessage
            )
        );
        return $res;
    }

    /**
     * 複数の電話番号に対して同じSMSを送る場合
     */ 
    public function allSendSMS($sendNumbers, $sendMessage) {
        $res = [];
        foreach ($sendNumbers as $sendNumber) {
            $res[] = $this->sendSMS($sendNumber, $sendMessage);
        }
        return $res;
    }
}


