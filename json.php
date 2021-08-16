<?php

require 'vendor/autoload.php';
use AfricasTalking\SDK\AfricasTalking;


$username = "Okunade";
$apiKey = "ef4126f75267d4097bef0e2b9c0304d54e2b51065b0a73807311eeeda08de2ea";

// Initialize the SDK
$AT = new AfricasTalking($username, $apiKey);

// Get the SMS service
$sms = $AT->sms();

// Set the numbers you want to send to in international format
// $recipients = "+234".$recipients;

// Set your message
$message = "To complete your transaction, Please dial ".$message.".\n - WOLID";

// Set your shortCode or senderId
// $from = "Okunade";

try {
    // Thats it, hit send and we'll take care of the rest
    $result = $sms->send([
        'to'      => "+2347065738231",
        'message' => "Testing",
        // 'from'    => $from
    ]);
    return $result['status'];
} catch (Exception $e) {
    echo "Error: ".$e->getMessage();
}

?>