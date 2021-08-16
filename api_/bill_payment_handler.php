<?php

class BillPayment{

  public function __construct()
  {
    
  }
  
  public function VerifyMeter($serviceID, $billersCode, $type){
    $fields = [
      'serviceID' => $serviceID,
      'billersCode' => $billersCode,
      'type' => $type
    ];
    
    $ch = curl_init();
    curl_setopt_array($ch, array(
      CURLOPT_URL => 'https://sandbox.vtpass.com/api/merchant-verify',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_USERPWD => "sandbox@vtpass.com:sandbox",
      CURLOPT_POST => true,
      CURLOPT_POSTFIELDS => $fields,
    ));
    
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    
    $response = curl_exec($ch);
    
    $trans_info = json_encode($response);
    echo $trans_info;
    // $result = json_decode($response);
    
    // $user->trans_token = isset($result->purchased_code) ? $result->purchased_code : 'null';
    // $tokenated = $result->purchased_code;
    // $user->response_status = $result->response_description;
    
  }

  public function PurchasePower($serviceID, $billersCode, $transaction_id, $type, $total_amount, $phone){
    $fields = [
        'serviceID' => $serviceID,
        'billersCode' => $billersCode,
        'request_id' => $transaction_id,
        'variation_code' => $type,
        'amount' => $total_amount,
        'phone' => $phone
    ];

    $ch = curl_init();
    curl_setopt_array($ch, array(
        CURLOPT_URL => 'https://sandbox.vtpass.com/api/pay',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_USERPWD => "sandbox@vtpass.com:sandbox",
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $fields,
    ));

    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

    $response = curl_exec($ch); 

    // $trans_info = json_encode($response);
    return $response;
  }

  public function PurchaseCable($serviceID, $billersCode, $variation_code, $amount, $phone, $request_id){
    $fields = [
        'serviceID' => $serviceID,
        'billersCode' => $billersCode,
        'request_id' => $request_id,
        'variation_code' => $variation_code,
        'amount' => $amount,
        'phone' => $phone
    ];

    $ch = curl_init();
    curl_setopt_array($ch, array(
        CURLOPT_URL => 'https://sandbox.vtpass.com/api/pay',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_USERPWD => "sandbox@vtpass.com:sandbox",
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $fields,
    ));

    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

    $response = curl_exec($ch); 

    // $trans_info = json_encode($response);
    return $response;
  }
}
