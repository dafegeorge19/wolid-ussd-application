<?php

//echo "its here";
//return;


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require "bill_payment_handler.php";
require 'vendor/autoload.php';
use AfricasTalking\SDK\AfricasTalking;
use GuzzleHttp\Psr7\Message;

require_once "constants.php";

class Activity{

    function cpost($urlxxx, $dataxxx) {
        $ch = curl_init($urlxxx);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataxxx);
        $rxxxxxxxx = curl_exec($ch);
        curl_close($ch);
        return $rxxxxxxxx;
    }

    public function BuyAirtime($destinationNumber, $senderNumber, $amount, $network){
        $vtu_code = "502";
        $apikey = "0b215eca15616eb4cc2bf5e38648c4123eccb295a7b508bb684e1c36a932d957";
        $serverid = "OKMTNAEKU";
        $ref = mt_rand(0000000000, 9999999999);
        $pin = "5555";
        $ussd = "*{$vtu_code}*1*1*{$destinationNumber}*{$amount}*{$pin}#";
        $postmtnair = [
            'server' => $serverid,
            'number' => $ussd,
            'ref' => $ref,
            'apikey' => $apikey,
            'sim'   => '1',
        ];
        $simhost_status = $this->cpost('https://simhostng.com/api/ussd',$postmtnair);

        $rest = json_decode($simhost_status, true);
        if($rest['data'][0]["response"] == "Ok"){
            //Send sms confirmation
            $username = "Okunade";
            $apiKey = "ef4126f75267d4097bef0e2b9c0304d54e2b51065b0a73807311eeeda08de2ea";
            $AT = new AfricasTalking($username, $apiKey);
            $sms = $AT->sms();
            $message = "You just purchase airtime of N{$amount} to {$destinationNumber}.\n-Thanks for choosing WOLID.";
            try {
                $result = $sms->send([
                    'to'      => $senderNumber,
                    'message' => $message,
                    // 'from'    => $from
                ]);
                return json_encode($result);
            } catch (Exception $e) {
                return $e->getMessage();
            }
        }

    }

    public function BuyData($destinationNumber, $senderNumber, $plan, $amt, $network){
        // MC~data~plan~amt~07065738231~+2347065738231~447123
        $vtu_code = "502";
        $apikey = "0b215eca15616eb4cc2bf5e38648c4123eccb295a7b508bb684e1c36a932d957";
        $serverid = "OKMTNAEKU";
        $ref = rand(0000000000, 9999999999);
        $pin = "5555";
        $ussd = "*{$vtu_code}*1*4*{$destinationNumber}*1*2*{$pin}#";
        $postmtnair = [
            'server' => $serverid,
            'number' => $ussd,
            'ref' => $ref,
            'apikey' => $apikey,
            'sim'   => '1',
        ];
        $simhost_status = $this->cpost('https://simhostng.com/api/ussd',$postmtnair);
        $rest = json_decode($simhost_status, true);
        if($rest['data'][0]["response"] == "Ok"){
            //Send sms confirmation
            $username = "Okunade";
            $apiKey = "ef4126f75267d4097bef0e2b9c0304d54e2b51065b0a73807311eeeda08de2ea";
            $AT = new AfricasTalking($username, $apiKey);
            $sms = $AT->sms();
            $message = "You just subscribe to data plan of {$plan} to {$destinationNumber}.\n-Thanks for choosing WOLID.";
            try {
                $result = $sms->send([
                    'to'      => $senderNumber,
                    'message' => $message,
                    // 'from'    => $from
                ]);
                return json_encode($result);
            } catch (Exception $e) {
                return $e->getMessage();
            }
        }
    }

    public function BuyCable($request_id, $serviceID, $billersCode, $variation_code, $amount,  $phone){

        $initPower = new BillPayment();
        $purchaseCable = $initPower->PurchaseCable($serviceID, $billersCode, $variation_code, $amount, $phone, $request_id);
        $result = json_decode($purchaseCable, true);

        if($result['code'] == "000"){
            //Send sms confirmation
            $username = "Okunade";
            $apiKey = "ef4126f75267d4097bef0e2b9c0304d54e2b51065b0a73807311eeeda08de2ea";
            $AT = new AfricasTalking($username, $apiKey);
            $sms = $AT->sms();
            $message = "You have successfully subscribe to {$variation_code}.\n\n-Thanks for choosing WOLID.";
            try {
                $result = $sms->send([
                    'to'      => $phone,
                    'message' => $message,
                    // 'from'    => $from
                ]);
                echo json_encode($result);
            } catch (Exception $e) {
                return $e->getMessage();
            }
        }
    }

    public function BuyPower($serviceID, $billersCode, $type, $total_amount, $phone){
        // MNC~power~50~1111111111111~ikeja-electric~prepaid~+2347065738231~277818

        $transaction_id = mt_rand(0000000000, 9999999999);

        $initPower = new BillPayment();
        $purchaseToken = $initPower->PurchasePower($serviceID, $billersCode, $transaction_id, $type, $total_amount, $phone);
        $result = json_decode($purchaseToken, true);
        $tokenated = $result['purchased_code'];
        if($result['code'] == "000"){
            //Send sms confirmation
            $username = "Okunade";
            $apiKey = "ef4126f75267d4097bef0e2b9c0304d54e2b51065b0a73807311eeeda08de2ea";
            $AT = new AfricasTalking($username, $apiKey);
            $sms = $AT->sms();
            $message = "Your {$tokenated}\nAmount: N{$total_amount},\nFrom: {$serviceID}.\n\n-Thanks for choosing WOLID.";
            try {
                $result = $sms->send([
                    'to'      => $phone,
                    'message' => $message,
                    // 'from'    => $from
                ]);
                echo json_encode($result);
            } catch (Exception $e) {
                return $e->getMessage();
            }
        }
    }

    public function UpdateWallet($amt_funded, $phone_number){
        // MC~wallet~50~+2348036554392~852716
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => API_URL."/update_acct_bal.php?phone_number={$phone_number}&amount={$amt_funded}",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        echo $response;
    }

    public function get_all_partner(){
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => API_URL.'/all-user.php',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
            ));
            $response = curl_exec($curl);
            curl_close($curl);
            return $response;
    }

    public function getPartnerPayment($org_id, $org_code){
        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => API_URL.'/fetch-partner-config.php?org_id='.$org_id.'&org_pay_code='.$org_code,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
      ));     

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;

    }


}