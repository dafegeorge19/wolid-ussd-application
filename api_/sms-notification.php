<?php
require 'vendor/autoload.php';
use AfricasTalking\SDK\AfricasTalking;
use GuzzleHttp\Psr7\Message;

class AfricasTalkingCustom{

    public function SendSms($recipients, $message){
        $username = "Okunade";
        $apiKey = "ef4126f75267d4097bef0e2b9c0304d54e2b51065b0a73807311eeeda08de2ea";
        $AT = new AfricasTalking($username, $apiKey);
        $sms = $AT->sms();
        $message = "To complete your transaction, Please dial ".$message.".\n - WOLID";
        try {
            $result = $sms->send([
                'to'      => $recipients,
                'message' => $message,
                // 'from'    => $from
            ]);
            return $result;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
}
?>