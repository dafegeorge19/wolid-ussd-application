<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function cpost($urlxxx, $dataxxx) {
  $ch = curl_init($urlxxx);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $dataxxx);
  $rxxxxxxxx = curl_exec($ch);
  curl_close($ch);
  return $rxxxxxxxx;
}

require '../vendor/autoload.php';
use AfricasTalking\SDK\AfricasTalking;
use GuzzleHttp\Psr7\Message;

include('bill_payment_handler.php');

$body = @file_get_contents("php://input");
http_response_code(200); 
$response = json_decode($body);
if ($response->data->status == 'successful') {
  $mfm = explode('~', $response->data->tx_ref);
  $subscriptionType = $mfm[1];

  if($subscriptionType == 'airtime'){
    $destinationNumber = $mfm[3];
    $senderNumber = $mfm[4];
    $amount = $mfm[2];
    $vtu_code = "456";
    // 2. Sharing for vending is 
    // *456*1*2*Amount*Phone nos*1*pin#
    $pin_code = "5050";
    $apikey = "0b215eca15616eb4cc2bf5e38648c4123eccb295a7b508bb684e1c36a932d957";
    $serverid = "OKMTNBDCD";
    $sim = '1';
    $ref = rand(0000000000, 9999999999);
    $pin = "5050";
    // $ussd= $vtu_code.'*1*2'.$amount.'*'.$destinationNumber.'*1*'.$pin.'#';
    $ussd = "*{$vtu_code}*1*2*{$amount}*{$destinationNumber}*1*{$pin}#";
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
    
  }elseif($subscriptionType == 'data'){

  }elseif($subscriptionType == 'gotv' || $subscriptionType == 'dstv'){

     // $whatToDo = "gotv~".$variation_code .'~'. $amtPaying . '~' . $billersCode . '~' . $phoneNumber .'~'. rand(000000, 999999);
    // MC~dstv~dstv-padi~50~1212121212~+2348036554392~926477

     $request_id = $mfm[6];
     $serviceID = $subscriptionType;
     $billersCode = $mfm[4];
     $variation_code = $mfm[2];
     $amount = $mfm[3];
    //  $phone = $mfm[5];
    $phone = "+2348036554392";

    //  echo "{$serviceID}, {$billersCode}, {$variation_code}, {$amount}, {$phone}, {$request_id}";
    //  dstv, 1212131212, dstv-padi, 50, +2348036554392, 898795
    //  return;

     $initPower = new BillPayment();
     $purchaseCable = $initPower->PurchaseCable($serviceID, $billersCode, $variation_code, $amount, $phone, $request_id);
     $result = json_decode($purchaseCable, true);

    //  var_dump($result);
    // echo $result['code'];
    //  return;

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

  }elseif($subscriptionType == 'power'){

    $distributor = $mfm[4];
    $meterno = $mfm[3];
    $transaction_id = rand(0000000000, 9999999999);
    $metertype = $mfm[5];
    // $total_amount = $mfm[2];
    $total_amount = 600;
    $phone = $mfm[6];

    $initPower = new BillPayment();
    $purchaseToken = $initPower->PurchasePower($distributor, $meterno, $transaction_id, $metertype, $total_amount, $phone);
    $result = json_decode($purchaseToken, true);
    $tokenated = $result['purchased_code'];
    if($result['code'] == "000"){
      //Send sms confirmation
      $username = "Okunade";
      $apiKey = "ef4126f75267d4097bef0e2b9c0304d54e2b51065b0a73807311eeeda08de2ea";
      $AT = new AfricasTalking($username, $apiKey);
      $sms = $AT->sms();
      $message = "Your {$tokenated}\nAmount: N{$total_amount},\nFrom: {$distributor}.\n\n-Thanks for choosing WOLID.";
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
  
}else{
  echo "error";
  return;
}
exit();