<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
    
include("load_wallet.php");
require_once "constants.php";
require "bill_payment_handler.php";
require '../vendor/autoload.php';
use AfricasTalking\SDK\AfricasTalking;
use GuzzleHttp\Psr7\Message;



function cpost($urlxxx, $dataxxx) {
  $ch = curl_init($urlxxx);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $dataxxx);
  $rxxxxxxxx = curl_exec($ch);
  curl_close($ch);
  return $rxxxxxxxx;
}



$body = @file_get_contents("php://input");
http_response_code(200); 
$response = json_decode($body);
if ($response->data->status == 'successful') {
$mfm = explode('~', $response->data->tx_ref);

$subscriptionType = $mfm[1];


  if($subscriptionType == 'airtime'){
    // MCF~airtime~100~07065738231~+2347065738231~896781
    $destinationNumber = $mfm[3];
    $senderNumber = $mfm[4];
    $amount = $mfm[2];
    // 2. Sharing for vending is 
    // *456*1*2*Amount*Phone nos*1*pin#
    //new *502*1*1*08062696191*10*5555#
    $vtu_code = "502";
    $apikey = "0b215eca15616eb4cc2bf5e38648c4123eccb295a7b508bb684e1c36a932d957";
    $serverid = "OKMTNAEKU";
    $sim = '1';
    $ref = rand(0000000000, 9999999999);
    $pin = "5555";
    //*502*1*1*50*07065738231*1*5555#
    // *502*1*1*receivernumber*amount*pin#
    //*502*1*1*08062696191*10*5555#
    $ussd = "*{$vtu_code}*1*1*{$destinationNumber}*{$amount}*{$pin}#";
    $postmtnair = [
        'server' => $serverid,
        'number' => $ussd,
        'ref' => $ref,
        'apikey' => $apikey,
        'sim'   => '1',
    ];
    $simhost_status = cpost('https://simhostng.com/api/ussd',$postmtnair);

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
          echo json_encode($result);
      } catch (Exception $e) {
          return $e->getMessage();
      }
    }
    
  }
  elseif($subscriptionType == 'data'){
    // MC~data~plan~amt~07065738231~+2347065738231~447123
    $destinationNumber = $mfm[4];
    $senderNumber = $mfm[5];
    $plan = $mfm[2];
    $amt = $mfm[3];

    $vtu_code = "502";
    $apikey = "0b215eca15616eb4cc2bf5e38648c4123eccb295a7b508bb684e1c36a932d957";
    $serverid = "OKMTNAEKU";
    $sim = '1';
    $ref = rand(0000000000, 9999999999);
    $pin = "5555";
    //*502*1*4*07065738231*1*2*5555#
    // $destinationNumber = "07065738231";
    $ussd = "*{$vtu_code}*1*4*{$destinationNumber}*1*2*{$pin}#";
    $postmtnair = [
        'server' => $serverid,
        'number' => $ussd,
        'ref' => $ref,
        'apikey' => $apikey,
        'sim'   => '1',
    ];
    $simhost_status = cpost('https://simhostng.com/api/ussd',$postmtnair);
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
          echo json_encode($result);
      } catch (Exception $e) {
          return $e->getMessage();
      }
    }


  }
  elseif($subscriptionType == 'dstv' || $subscriptionType == 'gotv'){
    // MNC~dstv~dstv-padi~50~1212121212~+2347065738231~389759
    // MNC!gotv~gotv-lite~410~1212121212~+2347065738231~218166

     // $whatToDo = "gotv~".$variation_code .'~'. $amtPaying . '~' . $billersCode . '~' . $phoneNumber .'~'. rand(000000, 999999);
    // MC~dstv~dstv-padi~50~1212121212~+2348036554392~926477

    $request_id = $mfm[6];
    $serviceID = $mfm[1];
    $billersCode = $mfm[4];
    $variation_code = $mfm[2];
    $amount = $mfm[3];
    //  $phone = $mfm[5];
    $phone = $mfm[5];

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
  elseif($subscriptionType == 'power'){
    // MNC~power~50~1111111111111~ikeja-electric~prepaid~+2347065738231~277818
    $serviceID = $mfm[4];
    $billersCode = $mfm[3];
    $transaction_id = rand(0000000000, 9999999999);
    $type = $mfm[5];
    // $total_amount = $mfm[2];
    $total_amount = 600;
    $phone = $mfm[6];

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
  elseif($subscriptionType == 'wallet'){
    
    // MC~wallet~50~+2348036554392~852716
    $amt_funded = $mfm[2];
    $phone_number = $mfm[3];
      $curl = curl_init();

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
  
  
}else{
  echo "error";
  return;
}
exit();