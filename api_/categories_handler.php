<?php
include_once "constants.php";

class BillerCategory{

  function getBillerList(){
    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => BASE_CATEGORY_URL,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "GET",
      CURLOPT_HTTPHEADER => array(
        "Authorization: Bearer ".BASE_SECRET_KEY,
        "Content-Type: application/json"
      ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    return $response;
  }

  function VtpassApi($serviceID, $billersCode, $requestID, $variationCode, $amount, $phone){

    $vtp_username = "sandbox@vtpass.com";
    $vtp_password = "sandbox";

    $fields = [
        'serviceID' => $serviceID,
        'billersCode' => $billersCode,
        'request_id' => $requestID,
        'variation_code' => $variationCode,
        'amount' => $amount,
        'phone' => $phone
    ];

    $ch = curl_init();
    curl_setopt_array($ch, array(
        CURLOPT_URL => VTPASS_URL,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_USERPWD => "{$vtp_username}:{$vtp_password}",
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $fields,
    ));

    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

    $response = curl_exec($ch);
    return $response;
  }

  function VtCategoryList($service){
    $vtp_username = "sandbox@vtpass.com";
    $vtp_password = "sandbox";

    $ch = curl_init();
    curl_setopt_array($ch, array(
        CURLOPT_URL => VTPASS_URL."service-variations?serviceID={$service}",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_USERPWD => "{$vtp_username}:{$vtp_password}",
        CURLOPT_POST => "GET",
        // CURLOPT_POSTFIELDS => $fields,
    ));

    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

    $response = curl_exec($ch);
    return $response;

  }

  function VerifyCable($serviceID, $smartCard){
    $fields = [
        'serviceID' => $serviceID,
        'billersCode' => $smartCard,
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

    return $response;
    
  }

  function VerifyPower($serviceID, $meterNo, $type){
    $fields = [
        'serviceID' => $serviceID,
        'billersCode' => $meterNo,
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

    return $response;
    
  }
}

?>