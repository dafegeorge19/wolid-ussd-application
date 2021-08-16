<?php

    include("sample-test-script.php");

    $encryptedData = $new->testingEncrypt("4000");

    $ch = curl_init();

    $headers  = ['Content-Type: text/plain'];

    curl_setopt($ch, CURLOPT_URL,"https://testdev.coralpay.com/cgateproxy/api/invokereference");

    curl_setopt($ch, CURLOPT_POST, 1);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    curl_setopt($ch, CURLOPT_POSTFIELDS, $encryptedData);

    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $result = curl_exec ($ch);

    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    $response = $new->testingDecrypt($result);
    print_r($response);

    // echo json_decode($response, true);

    // $decoded = json_decode($response, true);

    // echo $decoded['ResponseHeader']['ResponseMessage'];
    // echo $decoded['ResponseDetails']['Reference'];
?>