<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


require_once "api_/constants.php";
require_once "api_/categories_handler.php";
include("api_/flutterwave_payment.php");
include("api_/sms-notification.php");
include("api_/load_wallet.php");
include("api_/activity.php");


$biller_list = new BillerCategory();
$init = new FlutterPayment();
$wallet = new Wallet();
$activity = new Activity();


$getDSTV = $biller_list->VtCategoryList("dstv");
$getGoTV = $biller_list->VtCategoryList("gotv");
$all_partner = $activity->get_all_partner();

$dstv = json_decode($getDSTV, true);
$gotv = json_decode($getGoTV, true);
$all_partner = json_decode($all_partner, true);



$dstvList = $dstv["content"]["varations"];
$gotvList = $gotv["content"]["varations"];

$sessionId   = $_POST['sessionId'];
$serviceCode = $_POST['serviceCode'];
$text        = $_POST['text'];
$rootNumber = $_POST['phoneNumber'];

// use explode to split the string text response from Africa's talking gateway into an array.
$ussd_string_exploded = explode("*", $text);
// Get ussd menu level number from the gateway
$level = count($ussd_string_exploded);
if ($text == "") {
    $response="CON Welcome! Select an Option:\n";
    $response .= "1. Buy Airtime\n";
    $response .= "2. Buy Data \n";
    $response .= "3. Wallet \n";
    $response .= "4. Electricity Bill \n";
    $response .= "5. GoTV \n";
    $response .= "6. DSTV \n";
    $response .= "7. WAEC Card \n";
    // $response .= "8. Jamb Card \n";
    $response .= "8. Schools & Orgs \n";
}

/////////////////////////////////////////////////////////
//////////// Start of airtime purchase /////////////////
///////////////////////////////////////////////////////
elseif ($text == "1") {
    $country_code = "+234";
    $rootNumber = preg_replace("/^\+?{$country_code}/", '',$rootNumber);
    $rootNumber = "0".$rootNumber;

    $getUserWallet = $wallet->getUser($rootNumber);
    $wallet = json_decode($getUserWallet, true);
    $wallet_acct_bal = $wallet['account_balance'];
    $wallet_pin = $wallet['pin'];
    $wallet_status = $wallet['status'];


    $response  = "CON  Select network \n";
    $response .= "1. MTN \n";
    $response .= "2. GLO \n";
    $response .= "3. AIRTEL \n";
    $response .= "4. 9MOBILE \n";
    $response .= "0. Main Menu \n";
}
//for MTN recharge
elseif ($ussd_string_exploded[0] == 1 && $level == 2) {
    $response = "CON Enter amount:\n";
    $response .= "0. Main Menu \n";
}
elseif($ussd_string_exploded[0] == 1 && $level == 3){
    $response = "CON Enter Phone number /n (e.g. 08012345678) : \n";
    $response .= "0. Main Menu \n";
}
elseif($ussd_string_exploded[0] == 1 && $level == 4){
    $response = "CON Confirm airtime: N".$ussd_string_exploded[2]." \n";
    $response .= "For: ".$ussd_string_exploded[3]." \n";
    $response .= "Select Payment Type:\n";
    $response .= "1. Bank USSD \n";
    $response .= "2. Wallet \n";
    $response .= "3. Debit Card \n";
    $response .= "4. New Debit Card \n";
    $response .= "0. Main Menu \n";
}
//final payment using bank ussd
elseif( $ussd_string_exploded[0] == 1 && $level == 5 && $ussd_string_exploded[4] == "1" ){
    $response = "CON Select your bank \n";
    $response .= "1. First Bank of Nigeria \n";
    $response .= "2. Zenith Bank \n";
    $response .= "3. Standard Chartered Bank \n";
    $response .= "4. Fidelity Bank \n";
    $response .= "5. Unity Bank \n";
    $response .= "6. JAIZ Bank \n";
    $response .= "7. Ecobank Plc \n";
    $response .= "00. Next";
}
elseif( $ussd_string_exploded[0] == 1 && $level == 6 && $ussd_string_exploded[4] == "1" && $ussd_string_exploded[5] == "00" ){
    $response = "CON Select your bank \n";
    $response .= "1. Stanbic IBTC Bank \n";
    $response .= "2. Diamond Bank \n";
    $response .= "3. Heritage \n";
    $response .= "4. GTBank Plc \n";
    $response .= "5. Union Bank \n";
    $response .= "6. Sterling Bank \n";
    $response .= "7. Skye Bank \n";
    $response .= "00. Next";
}
elseif( $ussd_string_exploded[0] == 1 && $level == 6 && $ussd_string_exploded[4] == "1" && $ussd_string_exploded[5] != "00" ){
    // $response = "END You selected bank in level 5 \n";
    $destination_no = $ussd_string_exploded[3];
    $phoneNumber = preg_replace('/^0/','+234', $rootNumber);
    $amtPaying = $ussd_string_exploded[2];
    switch($ussd_string_exploded[5]){
        case "1": //firstbank
            $bankCode = "137";
            break;
        case "2": //Zenith Bank
            $bankCode = "141";
            break;
        case "3": //Standard Chartered Bank
            $bankCode = "142";
            break;
        case "4": //Fidelity Bank
            $bankCode = "144";
            break;
        case "5": //Unity Bank
            $bankCode = "146";
            break;
        case "6": //JAIZ Bank
            $bankCode = "151";
            break;
        default: //Ecobank Plc
            $bankCode = "152";
    }
    $whatToDo = "airtime~".$ussd_string_exploded[2] . '~' . $destination_no . '~' . $phoneNumber .'~'. rand(000000, 999999);
    $result = $init->MakePayment($amtPaying, $bankCode, $phoneNumber, $whatToDo);
    $rest = json_decode($result, TRUE);
    if($rest["status"] == "success"){
        $content = $rest["meta"]["authorization"]["note"] .". Your bank code is: ". $bankCode;
        $ATsms = new AfricasTalkingCustom();
        $result = $ATsms->SendSms($phoneNumber, $content);
        $res = json_encode($result["data"]);
        $final_res = json_decode($res, true);
        if($final_res["SMSMessageData"]["Recipients"][0]["status"] == "Success"){
            $response = "END To complete this transaction, please dial: ". $rest["meta"]["authorization"]["note"];
        }else{
            $response = "END Problem sending sms, Please dial: " . $rest["meta"]["authorization"]["note"] . "to complete this transaction. Your bank code is : {$bankCode}";
        }
    }else{
        $response = "END Error initiating transaction, please try again!";
    }
}
elseif($ussd_string_exploded[0] == 1 && $level == 7 && $ussd_string_exploded[4] == "1" && $ussd_string_exploded[6] == "00"){
    $response = "CON Select your bank\n";
    $response .= "1. Keystone Bank \n";
    $response .= "2. Fidelity  \n";
    $response .= "3. First City Monument Bank \n";
    $response .= "4. United Bank for Africa \n";
    $response .= "5. Access Bank";
    $response .= "6. Wema Bank \n";
}
elseif( $ussd_string_exploded[0] == 1 && $level == 7 && $ussd_string_exploded[4] == "1" && $ussd_string_exploded[6] != "00" ){
    // $response = "END You selected bank in level 6 \n";
    switch($ussd_string_exploded[6]){
        case "1": //Stanbic IBTC Bank
            $bankCode = "158";
            break;
        case "2": //Diamond Bank
            $bankCode = "170";
            break;
        case "3": //Heritage
            $bankCode = "175";
            break;
        case "4": //GTBank Plc
            $bankCode = "177";
            break;
        case "5": //Union Bank
            $bankCode = "178";
            break;
        case "6": //Sterling Bank
            $bankCode = "179";
            break;
        default: //Skye Bank
            $bankCode = "180";
    }
    $destination_no = $ussd_string_exploded[3];
    $phoneNumber = preg_replace('/^0/','+234', $rootNumber);
    $amtPaying = $ussd_string_exploded[2];
    $whatToDo = "airtime~".$ussd_string_exploded[2] . '~' . $destination_no . '~' . $phoneNumber .'~'. rand(000000, 999999);
    $result = $init->MakePayment($amtPaying, $bankCode, $phoneNumber, $whatToDo);
    $rest = json_decode($result, TRUE);
    if($rest["status"] == "success"){
        $content = $rest["meta"]["authorization"]["note"] .". Your bank code is: ". $bankCode;
        $ATsms = new AfricasTalkingCustom();
        $result = $ATsms->SendSms($phoneNumber, $content);
        $res = json_encode($result["data"]);
        $final_res = json_decode($res, true);
        if($final_res["SMSMessageData"]["Recipients"][0]["status"] == "Success"){
            $response = "END To complete this transaction, please dial: ". $rest["meta"]["authorization"]["note"];
        }else{
            $response = "END Problem sending sms, Please dial: " . $rest["meta"]["authorization"]["note"] . "to complete this transaction. Your bank code is : {$bankCode}";
        }
    }else{
        $response = "END Error initiating transaction, please try again!";
    }
}
elseif($ussd_string_exploded[0] == 1 && $ussd_string_exploded[4] == "1" && $level == 8){
    switch($ussd_string_exploded[6]){
        case "1": //Keystone Bank
            $bankCode = "181";
            break;
        case "2": //Fidelity
            $bankCode = "144";
            break;
        case "3": //First City Monument Bank
            $bankCode = "186";
            break;
        case "4": //United Bank for Africa
            $bankCode = "190";
            break;
        case "5": //Access Bank
            $bankCode = "191";
            break;
        default: //Wema Bank
            $bankCode = "168";
    }
    $destination_no = $ussd_string_exploded[3];
    $phoneNumber = preg_replace('/^0/','+234', $rootNumber);
    $amtPaying = $ussd_string_exploded[2];
    $whatToDo = "airtime~".$ussd_string_exploded[2] . '~' . $destination_no . '~' . $phoneNumber .'~'. rand(000000, 999999);
    $result = $init->MakePayment($amtPaying, $bankCode, $phoneNumber, $whatToDo);
    $rest = json_decode($result, TRUE);
    if($rest["status"] == "success"){
        $content = $rest["meta"]["authorization"]["note"] .". Your bank code is: ". $bankCode;
        $ATsms = new AfricasTalkingCustom();
        $result = $ATsms->SendSms($phoneNumber, $content);
        $res = json_encode($result["data"]);
        $final_res = json_decode($res, true);
        if($final_res["SMSMessageData"]["Recipients"][0]["status"] == "Success"){
            $response = "END To complete this transaction, please dial: ". $rest["meta"]["authorization"]["note"];
        }else{
            $response = "END Problem sending sms, Please dial: " . $rest["meta"]["authorization"]["note"] . "to complete this transaction. Your bank code is : {$bankCode}";
        }
    }else{
        $response = "END Error initiating transaction, please try again!";
    }
}
elseif( $ussd_string_exploded[0] == 1 && $level == 5 && $ussd_string_exploded[4] == "2" ){
    $country_code = "+234";
    $rootNumber = preg_replace("/^\+?{$country_code}/", '',$rootNumber);
    $rootNumber = "0".$rootNumber;

    $getUserWallet = $wallet->getUser($rootNumber);
    $wallet = json_decode($getUserWallet, true);
    $wallet_acct_bal = $wallet['account_balance'];
    $wallet_pin = $wallet['pin'];
    $wallet_status = $wallet['status'];

    $response = "CON Your wallet balance: {$wallet_acct_bal}\n";
    $response .= "1. Proceed \n";
    $response .= "2. Cancel \n";
    $response .= "0. Main Menu \n";
}
//Final payment through walleting
elseif($ussd_string_exploded[0] == 1 && $level == 6 && $ussd_string_exploded[4] == "2"){

//    $country_code = "+234";
//    $rootNumber = preg_replace("/^\+?{$country_code}/", '',$rootNumber);
//    $rootNumber = "0".$rootNumber;

    $getUserWallet = $wallet->getUser($rootNumber);
    $wallet = json_decode($getUserWallet, true);
    $wallet_acct_bal = $wallet['account_balance'];
    $wallet_pin = $wallet['pin'];
    $wallet_status = $wallet['status'];

    $destination_no = $ussd_string_exploded[3];
//    $phoneNumber = preg_replace('/^0/','+234', $rootNumber);
    $amtPaying = $ussd_string_exploded[2];

//    $response = "END {$rootNumber}, {$destination_no}";

    if($amtPaying < $wallet_acct_bal){
        $buyTruWallet = $activity->BuyAirtime($destination_no, $rootNumber, $amtPaying, $ussd_string_exploded[1]);
//        $buyTruWallet = $activity->BuyAirtime("+2347065738231", "+2347065738231", "300", "MTN");
        $result = json_decode($buyTruWallet, true);
        if($result['status'] == "success"){
            $response = "END You request is processing.\n\nThanks for choosing WOLID.";
        }else{
            $response = "END Could not process your request, please again later.\n\n-WOLID.";
        }
    }else{
        $response = "END You do not have enough balance in your wallet. Fund your wallet or buy using through bank.";
    }

}
///////////////////////////////////////////////////////////
////////////// End of airtime purchase ///////////////////
/////////////////////////////////////////////////////////





///////////////////////////////////////////////////////
////////////// Start of Data purchase ////////////////
/////////////////////////////////////////////////////
elseif ($text == "2") {
    $country_code = "+234";
    $rootNumber = preg_replace("/^\+?{$country_code}/", '',$rootNumber);
    $rootNumber = "0".$rootNumber;

    $getUserWallet = $wallet->getUser($rootNumber);
    $wallet = json_decode($getUserWallet, true);
    $wallet_acct_bal = $wallet['account_balance'];
    $wallet_pin = $wallet['pin'];
    $wallet_status = $wallet['status'];

    
    $response  = "CON  Select network: \n";
    $response .= "1. MTN \n";
    $response .= "2. GLO \n";
    $response .= "3. AIRTEL \n";
    $response .= "4. 9MOBILE \n";
    $response .= "0. Main Menu \n";
}
//for MTN recharge
elseif ($ussd_string_exploded[0] == 2 && $level == 2) {
    $response = "CON Choose data plan: \n";
    $response .= "1. 40MB \n";
    $response .= "2. 500MB \n";
    $response .= "3. 1GB \n";
    $response .= "4. 2GB \n";
    $response .= "5. 3GB \n";
    $response .= "6. 5GB \n";
    $response .= "0. Main Menu \n";
}
elseif($ussd_string_exploded[0] == 2 && $level == 3){
    $response = "CON Enter recipient phone number: \n";
    $response .= "0. Main Menu \n";
}
elseif($ussd_string_exploded[0] == 2 && $level == 4){
    switch($ussd_string_exploded[2]){
        case 1:
            $dtplan = "40MB";
        break;
        case 2:
            $dtplan = "500MB";
        break;
        case 3:
            $dtplan = "1GB";
        break;
        case 4:
            $dtplan = "2GB";
        break;
        case 5:
            $dtplan = "3GB";
        break;
        default:
            $dtplan = "5GB";
    }
    $response = "CON Confirm data plan: ".$dtplan.". \n";
    $response .= "To: ".$ussd_string_exploded[3]."\n";
    $response .= "Select Payment Type \n";
    $response .= "1. Bank USSD \n";
    $response .= "2. Wallet \n";
    $response .= "3. Debit Card \n";
    $response .= "4. New Debit Card \n";
    $response .= "0. Main Menu \n";
}
//final payment using bank ussd
elseif( $ussd_string_exploded[0] == 2 && $level == 5 && $ussd_string_exploded[4] == "1" ){
    $response = "CON Select your bank \n";
    $response .= "1. First Bank of Nigeria \n";
    $response .= "2. Zenith Bank \n";
    $response .= "3. Standard Chartered Bank \n";
    $response .= "4. Fidelity Bank \n";
    $response .= "5. Unity Bank \n";
    $response .= "6. JAIZ Bank \n";
    $response .= "7. Ecobank Plc \n";
    $response .= "00. Next";
}
elseif( $ussd_string_exploded[0] == 2 && $level == 6 && $ussd_string_exploded[4] == "1" && $ussd_string_exploded[5] == "00" ){
    $response = "CON Select your bank \n";
    $response .= "1. Stanbic IBTC Bank \n";
    $response .= "2. Diamond Bank \n";
    $response .= "3. Heritage \n";
    $response .= "4. GTBank Plc \n";
    $response .= "5. Union Bank \n";
    $response .= "6. Sterling Bank \n";
    $response .= "7. Skye Bank \n";
    $response .= "00. Next";
}
elseif( $ussd_string_exploded[0] == 2 && $level == 6 && $ussd_string_exploded[4] == "1" && $ussd_string_exploded[5] != "00" ){
    // $response = "END You selected bank in level 5 \n";
    $destination_no = $ussd_string_exploded[3];
    $phoneNumber = preg_replace('/^0/','+234', $rootNumber);
    switch($ussd_string_exploded[2]){
        case 1:
            $dtplan = "40MB";
            $dtamt = "50";
        break;
        case 2:
            $dtplan = "500MB";
            $dtamt = "200";
        break;
        case 3:
            $dtplan = "1GB";
            $dtamt = "400";
        break;
        case 4:
            $dtplan = "2GB";
            $dtamt = "750";
        break;
        case 5:
            $dtplan = "3GB";
            $dtamt = "1000";
        break;
        default:
            $dtplan = "5GB";
            $dtamt = "1500";
    }
    switch($ussd_string_exploded[5]){
        case "1": //firstbank
            $bankCode = "137";
            break;
        case "2": //Zenith Bank
            $bankCode = "141";
            break;
        case "3": //Standard Chartered Bank
            $bankCode = "142";
            break;
        case "4": //Fidelity Bank
            $bankCode = "144";
            break;
        case "5": //Unity Bank
            $bankCode = "146";
            break;
        case "6": //JAIZ Bank
            $bankCode = "151";
            break;
        default: //Ecobank Plc
            $bankCode = "152";
    }
    $whatToDo = "data~".$dtplan. '~' .$dtamt. '~' . $destination_no . '~' . $phoneNumber .'~'. rand(000000, 999999);
    // $response = "END {$whatToDo}";
    $result = $init->MakePayment($dtamt, $bankCode, $phoneNumber, $whatToDo);
    $rest = json_decode($result, TRUE);
    if($rest["status"] == "success"){
        $content = $rest["meta"]["authorization"]["note"];
        $ATsms = new AfricasTalkingCustom();
        $result = $ATsms->SendSms($phoneNumber, $content);
        $res = json_encode($result["data"]);
        $final_res = json_decode($res, true);
        if($final_res["SMSMessageData"]["Recipients"][0]["status"] == "Success"){
            $response = "END To complete this transaction, please dial: ". $rest["meta"]["authorization"]["note"];
        }else{
            $response = "END Problem sending sms, Please dial: " . $rest["meta"]["authorization"]["note"] . "to complete this transaction.";
        }
    }else{
        $response = "END Error initiating transaction, please try again!";
    }
}
elseif($ussd_string_exploded[0] == 2 && $level == 7 && $ussd_string_exploded[4] == "1" && $ussd_string_exploded[6] == "00"){
    $response = "CON Select your bank\n";
    $response .= "1. Keystone Bank \n";
    $response .= "2. Fidelity  \n";
    $response .= "3. First City Monument Bank \n";
    $response .= "4. United Bank for Africa \n";
    $response .= "5. Access Bank";
    $response .= "6. Wema Bank \n";
}
elseif( $ussd_string_exploded[0] == 2 && $level == 7 && $ussd_string_exploded[4] == "1" && $ussd_string_exploded[6] != "00" ){
    // $response = "END You selected bank in level 6 \n";
    switch($ussd_string_exploded[2]){
        case 1:
            $dtplan = "40MB";
            $dtamt = "50";
        break;
        case 2:
            $dtplan = "500MB";
            $dtamt = "200";
        break;
        case 3:
            $dtplan = "1GB";
            $dtamt = "400";
        break;
        case 4:
            $dtplan = "2GB";
            $dtamt = "750";
        break;
        case 5:
            $dtplan = "3GB";
            $dtamt = "1000";
        break;
        default:
            $dtplan = "5GB";
            $dtamt = "1500";
    }
    switch($ussd_string_exploded[6]){
        case "1": //Stanbic IBTC Bank
            $bankCode = "158";
            break;
        case "2": //Diamond Bank
            $bankCode = "170";
            break;
        case "3": //Heritage
            $bankCode = "175";
            break;
        case "4": //GTBank Plc
            $bankCode = "177";
            break;
        case "5": //Union Bank
            $bankCode = "178";
            break;
        case "6": //Sterling Bank
            $bankCode = "179";
            break;
        default: //Skye Bank
            $bankCode = "180";
    }
    $destination_no = $ussd_string_exploded[3];
    $phoneNumber = preg_replace('/^0/','+234', $rootNumber);
    $whatToDo = "data~".$dtplan. '~' .$dtamt. '~' . $destination_no . '~' . $phoneNumber .'~'. rand(000000, 999999);
    // $response = "END {$whatToDo}";
    $result = $init->MakePayment($dtamt, $bankCode, $phoneNumber, $whatToDo);
    $rest = json_decode($result, TRUE);
    if($rest["status"] == "success"){
        $content = $rest["meta"]["authorization"]["note"];
        $ATsms = new AfricasTalkingCustom();
        $result = $ATsms->SendSms($phoneNumber, $content);
        $res = json_encode($result["data"]);
        $final_res = json_decode($res, true);
        if($final_res["SMSMessageData"]["Recipients"][0]["status"] == "Success"){
            $response = "END To complete this transaction, please dial: ". $rest["meta"]["authorization"]["note"];
        }else{
            $response = "END Problem sending sms, Please dial: " . $rest["meta"]["authorization"]["note"] . "to complete this transaction.";
        }
    }else{
        $response = "END Error initiating transaction, please try again!";
    }
}
elseif($ussd_string_exploded[0] == 2 && $ussd_string_exploded[4] == "1" && $level == 8){
    switch($ussd_string_exploded[6]){
        case "1": //Keystone Bank
            $bankCode = "181";
            break;
        case "2": //Fidelity
            $bankCode = "144";
            break;
        case "3": //First City Monument Bank
            $bankCode = "186";
            break;
        case "4": //United Bank for Africa
            $bankCode = "190";
            break;
        case "5": //Access Bank
            $bankCode = "191";
            break;
        default: //Wema Bank
            $bankCode = "168";
    }
    switch($ussd_string_exploded[2]){
        case 1:
            $dtplan = "40MB";
            $dtamt = "50";
        break;
        case 2:
            $dtplan = "500MB";
            $dtamt = "200";
        break;
        case 3:
            $dtplan = "1GB";
            $dtamt = "400";
        break;
        case 4:
            $dtplan = "2GB";
            $dtamt = "750";
        break;
        case 5:
            $dtplan = "3GB";
            $dtamt = "1000";
        break;
        default:
            $dtplan = "5GB";
            $dtamt = "1500";
    }
    $destination_no = $ussd_string_exploded[3];
    $phoneNumber = preg_replace('/^0/','+234', $rootNumber);
    $whatToDo = "data~".$dtplan. '~' .$dtamt. '~' . $destination_no . '~' . $phoneNumber .'~'. rand(000000, 999999);
    // $response = "END {$whatToDo}";
    $result = $init->MakePayment($dtamt, $bankCode, $phoneNumber, $whatToDo);
    $rest = json_decode($result, TRUE);
    if($rest["status"] == "success"){
        $content = $rest["meta"]["authorization"]["note"];
        $ATsms = new AfricasTalkingCustom();
        $result = $ATsms->SendSms($phoneNumber, $content);
        $res = json_encode($result["data"]);
        $final_res = json_decode($res, true);
        if($final_res["SMSMessageData"]["Recipients"][0]["status"] == "Success"){
            $response = "END To complete this transaction, please dial: ". $rest["meta"]["authorization"]["note"];
        }else{
            $response = "END Problem sending sms, Please dial: " . $rest["meta"]["authorization"]["note"] . "to complete this transaction.";
        }
    }else{
        $response = "END Error initiating transaction, please try again!";
    }
}
elseif( $ussd_string_exploded[0] == 2 && $level == 5 && $ussd_string_exploded[4] == "2" ){
    $country_code = "+234";
    $rootNumber = preg_replace("/^\+?{$country_code}/", '',$rootNumber);
    $rootNumber = "0".$rootNumber;

    $getUserWallet = $wallet->getUser($rootNumber);
    $wallet = json_decode($getUserWallet, true);
    $wallet_acct_bal = $wallet['account_balance'];
    $wallet_pin = $wallet['pin'];
    $wallet_status = $wallet['status'];

    $response = "CON Your wallet balance: {$wallet_acct_bal}\n";
    $response .= "1. Proceed \n";
    $response .= "2. Cancel \n";
    $response .= "0. Main Menu \n";
}
//Final payment through walleting
elseif($ussd_string_exploded[0] == 2 && $level == 6 && $ussd_string_exploded[4] == "2"){

    $getUserWallet = $wallet->getUser($rootNumber);
    $wallet = json_decode($getUserWallet, true);
    $wallet_acct_bal = $wallet['account_balance'];
    $wallet_pin = $wallet['pin'];
    $wallet_status = $wallet['status'];

    switch($ussd_string_exploded[2]){
        case 1:
            $dtplan = "40MB";
            $dtamt = "50";
            break;
        case 2:
            $dtplan = "500MB";
            $dtamt = "200";
            break;
        case 3:
            $dtplan = "1GB";
            $dtamt = "400";
            break;
        case 4:
            $dtplan = "2GB";
            $dtamt = "750";
            break;
        case 5:
            $dtplan = "3GB";
            $dtamt = "1000";
            break;
        default:
            $dtplan = "5GB";
            $dtamt = "1500";
    }

    $destination_no = $ussd_string_exploded[3];

    if($dtamt < $wallet_acct_bal){
        $buyTruWallet = $activity->BuyData($destination_no, $rootNumber, $dtplan, $dtamt, $ussd_string_exploded[1]);
        $result = json_decode($buyTruWallet, true);
        if($result['status'] == "success"){
            $response = "END You request is processing.\n\nThanks for choosing WOLID.";
        }else{
            $response = "END Could not process your request, please again later.\n\n-WOLID.";
        }
    }else{
        $response = "END You do not have enough balance in your wallet. Fund your wallet or buy using through bank.";
    }
//    if($ussd_string_exploded[1] == 1){
//        $network = "MTN";
//    }elseif($ussd_string_exploded[1] == 2){
//        $network = "GLO";
//    }elseif($ussd_string_exploded[1] == 3){
//        $network = "Airtel";
//    }
//    elseif($ussd_string_exploded[1] == 4){
//        $network = "9Mobile";
//    }
//    $response = "END You are about to purchase N".$ussd_string_exploded[2]." ".$network." airtime, recipient: ".$ussd_string_exploded[3].". Please wait while you are being redirected to make payment \n";
}
//////////////////////////////////////////////////////
////////////// End of Data purchase /////////////////
////////////////////////////////////////////////////





////////////////////////////////////////////////////
////////////// Start of Walleting /////////////////
//////////////////////////////////////////////////

else if($text == "3") {  
    $response  = "CON  Select action \n";
    $response .= "1. Check Wallet balance \n";
    $response .= "2. Fund Wallet \n";
    $response .= "3. Change PIN ";
}
//to check wallet balance
elseif ($ussd_string_exploded[0] == 3 && $ussd_string_exploded[1] == 1 && $level == 2) {
    $response = "CON Input your 4 digit pin: \n";
    $response .= "0. Main Menu \n";
}
elseif($ussd_string_exploded[0] == 3 && $ussd_string_exploded[1] == 1 && $level == 3){
    $country_code = "+234";
    $rootNumber = preg_replace("/^\+?{$country_code}/", '',$rootNumber);
    $rootNumber = "0".$rootNumber;

    $getUserWallet = $wallet->getUser($rootNumber);
    $wallet = json_decode($getUserWallet, true);
    $wallet_acct_bal = $wallet['account_balance'];
    $wallet_pin = $wallet['pin'];
    $wallet_status = $wallet['status'];

    if($ussd_string_exploded[2] == $wallet_pin){
        $response = "END Your wallet balance is: N{$wallet_acct_bal}\n";
    }else{
        $response = "END Wrong pin, please try again later.";
    }
}
//to fund wallet
elseif($ussd_string_exploded[0] == 3 && $ussd_string_exploded[1] == 2 && $level == 2){
    $response = "CON Amount to fund: \n";
    $response .= "0. Main Menu \n";
}
elseif($ussd_string_exploded[0] == 3 && $ussd_string_exploded[1] == 2 && $level == 3 ){
    $response = "CON Select Payment Type \n";
    $response .= "1. Bank USSD \n";
    $response .= "2. Debit Card \n";
    $response .= "3. New Debit Card \n";
}
//final payment using bank ussd
elseif( $ussd_string_exploded[0] == 3 && $ussd_string_exploded[1] == 2 && $level == 4 && $ussd_string_exploded[3] == "1" ){
    $response = "CON Select your bank \n";
    $response .= "1. First Bank of Nigeria \n";
    $response .= "2. Zenith Bank \n";
    $response .= "3. Standard Chartered Bank \n";
    $response .= "4. Fidelity Bank \n";
    $response .= "5. Unity Bank \n";
    $response .= "6. JAIZ Bank \n";
    $response .= "7. Ecobank Plc \n";
    $response .= "00. Next";
}
elseif( $ussd_string_exploded[0] == 3 && $level == 5 && $ussd_string_exploded[3] == "1" && $ussd_string_exploded[4] != "00" ){
    $country_code = "+234";
    $rootNum = $rootNumber;
    $rootNumber = preg_replace("/^\+?{$country_code}/", '',$rootNumber);
    $phoneNumber = "0".$rootNumber;

    switch($ussd_string_exploded[4]){
        case "1": //firstbank
            $bankCode = "137";
            break;
        case "2": //Zenith Bank
            $bankCode = "141";
            break;
        case "3": //Standard Chartered Bank
            $bankCode = "142";
            break;
        case "4": //Fidelity Bank
            $bankCode = "144";
            break;
        case "5": //Unity Bank
            $bankCode = "146";
            break;
        case "6": //JAIZ Bank
            $bankCode = "151";
            break;
        default: //Ecobank Plc
            $bankCode = "152";
    }
    $whatToDo = "wallet~".$ussd_string_exploded[2]. '~' . $phoneNumber .'~'. rand(000000, 999999);
    $result = $init->MakePayment($ussd_string_exploded[2], $bankCode, $rootNum, $whatToDo);
    $rest = json_decode($result, TRUE);
    if($rest["status"] == "success"){
        $content = $rest["meta"]["authorization"]["note"];
        $ATsms = new AfricasTalkingCustom();
        $result = $ATsms->SendSms($rootNum, $content);
        $res = json_encode($result["data"]);
        $final_res = json_decode($res, true);
        if($final_res["SMSMessageData"]["Recipients"][0]["status"] == "Success"){
            $response = "END To complete this transaction, please dial: ". $rest["meta"]["authorization"]["note"];
        }else{
            $response = "END Problem sending sms, Please dial: " . $rest["meta"]["authorization"]["note"] . "to complete this transaction.";
        }
    }else{
        $response = "END Error initiating transaction, please try again!";
    }
}
elseif( $ussd_string_exploded[0] == 3 && $level == 5 && $ussd_string_exploded[3] == "1" && $ussd_string_exploded[4] == "00" ){
    $response = "CON Select your bank \n";
    $response .= "1. Stanbic IBTC Bank \n";
    $response .= "2. Diamond Bank \n";
    $response .= "3. Heritage \n";
    $response .= "4. GTBank Plc \n";
    $response .= "5. Union Bank \n";
    $response .= "6. Sterling Bank \n";
    $response .= "7. Skye Bank \n";
    $response .= "00. Next";
}
elseif( $ussd_string_exploded[0] == 3 && $level == 6 && $ussd_string_exploded[3] == "1" && $ussd_string_exploded[5] != "00" ){
    // $response = "END You selected bank in level 6 \n";
    switch($ussd_string_exploded[5]){
        case "1": //Stanbic IBTC Bank
            $bankCode = "158";
            break;
        case "2": //Diamond Bank
            $bankCode = "170";
            break;
        case "3": //Heritage
            $bankCode = "175";
            break;
        case "4": //GTBank Plc
            $bankCode = "177";
            break;
        case "5": //Union Bank
            $bankCode = "178";
            break;
        case "6": //Sterling Bank
            $bankCode = "179";
            break;
        default: //Skye Bank
            $bankCode = "180";
    }
    $rootNum = $rootNumber;
    $country_code = "+234";
    $rootNumber = preg_replace("/^\+?{$country_code}/", '',$rootNumber);
    $phoneNumber = "0".$rootNumber;
    
    $whatToDo = "wallet~".$ussd_string_exploded[2]. '~' . $phoneNumber .'~'. rand(000000, 999999);
    $result = $init->MakePayment($ussd_string_exploded[2], $bankCode, $rootNum, $whatToDo);
    $rest = json_decode($result, TRUE);
    if($rest["status"] == "success"){
        $content = $rest["meta"]["authorization"]["note"];
        $ATsms = new AfricasTalkingCustom();
        $result = $ATsms->SendSms($rootNum, $content);
        $res = json_encode($result["data"]);
        $final_res = json_decode($res, true);
        if($final_res["SMSMessageData"]["Recipients"][0]["status"] == "Success"){
            $response = "END To complete this transaction, please dial: ". $rest["meta"]["authorization"]["note"];
        }else{
            $response = "END Problem sending sms, Please dial: " . $rest["meta"]["authorization"]["note"] . "to complete this transaction.";
        }
    }else{
        $response = "END Error initiating transaction, please try again!";
    }
}
elseif($ussd_string_exploded[0] == 3 && $level == 6 && $ussd_string_exploded[3] == "1" && $ussd_string_exploded[5] == "00"){
    $response = "CON Select your bank\n";
    $response .= "1. Keystone Bank \n";
    $response .= "2. Fidelity  \n";
    $response .= "3. First City Monument Bank \n";
    $response .= "4. United Bank for Africa \n";
    $response .= "5. Access Bank \n";
    $response .= "6. Wema Bank \n";
}
elseif($ussd_string_exploded[0] == 3  && $level == 7){
    switch($ussd_string_exploded[6]){
        case "1": //Keystone Bank
            $bankCode = "181";
            break;
        case "2": //Fidelity
            $bankCode = "144";
            break;
        case "3": //First City Monument Bank
            $bankCode = "186";
            break;
        case "4": //United Bank for Africa
            $bankCode = "190";
            break;
        case "5": //Access Bank
            $bankCode = "191";
            break;
        default: //Wema Bank
            $bankCode = "168";
    }
    $rootNum = $rootNumber;
    $country_code = "+234";
    $rootNumber = preg_replace("/^\+?{$country_code}/", '',$rootNumber);
    $phoneNumber = "0".$rootNumber;
    
    $whatToDo = "wallet~".$ussd_string_exploded[2]. '~' . $phoneNumber .'~'. rand(000000, 999999);
    $result = $init->MakePayment($ussd_string_exploded[2], $bankCode, $rootNum, $whatToDo);
    $rest = json_decode($result, TRUE);
    if($rest["status"] == "success"){
        $content = $rest["meta"]["authorization"]["note"];
        $ATsms = new AfricasTalkingCustom();
        $result = $ATsms->SendSms($rootNum, $content);
        $res = json_encode($result["data"]);
        $final_res = json_decode($res, true);
        if($final_res["SMSMessageData"]["Recipients"][0]["status"] == "Success"){
            $response = "END To complete this transaction, please dial: ". $rest["meta"]["authorization"]["note"];
        }else{
            $response = "END Problem sending sms, Please dial: " . $rest["meta"]["authorization"]["note"] . "to complete this transaction.";
        }
    }else{
        $response = "END Error initiating transaction, please try again!";
    }
}
elseif ($ussd_string_exploded[0] == 3 && $ussd_string_exploded[1] == 3 && $level == 2) {
    $response = "CON Input your Old 4 Digit Pin: \n";
    $response .= "0. Main Menu \n";
}
elseif ($ussd_string_exploded[0] == 3 && $ussd_string_exploded[1] == 3 && $level == 3) {
    $response = "CON Input your New 4 Digit Pin: \n";
    $response .= "0. Main Menu \n";
}
elseif ($ussd_string_exploded[0] == 3 && $ussd_string_exploded[1] == 3 && $level == 4) {
    $country_code = "+234";
    $rootNumber = preg_replace("/^\+?{$country_code}/", '',$rootNumber);
    $phoneNumber = "0".$rootNumber;

    $getUserWallet = $wallet->getUser($phoneNumber);
    $wallet = json_decode($getUserWallet, true);
    $wallet_acct_bal = $wallet['account_balance'];
    $wallet_pin = $wallet['pin'];
    $wallet_status = $wallet['status'];
    
    // $response = "END Password match {$phoneNumber}, {$ussd_string_exploded[2]}, {$wallet_acct_bal}, {$wallet_pin}, {$wallet_status} ";

    if($wallet_pin == $ussd_string_exploded[2]){
        // $response = "END Password does match!";
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "http://justadreammovie.com/wolid-api/api/update_pin.php?phone_number={$phoneNumber}&pin={$ussd_string_exploded[3]}",
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
        $response = "End Pin successfully change!";
        
    }else{
        $response = "END Your pin did not MATCH!";
    }
}
////////////////////////////////////////////////////
////////////// End of Walleting /////////////////
//////////////////////////////////////////////////





////////////////////////////////////////////////
////////////// Start of electricity bill //////
//////////////////////////////////////////////
else if($text == "4") {

    $response  = "CON  Select option: \n";
    $response .= "1. IKEDC \n";
    $response .= "2. KEDC \n";
    $response .= "3. KEDCO \n";
    $response .= "4. PHED \n";
    $response .= "5. JED \n";
    $response .= "6. IBEDC \n";
    $response .= "7. KAEDCO \n";
    $response .= "8. AEDC  \n";
    $response .= "0. Main Menu";
}
elseif($ussd_string_exploded[0] == 4 && $level == 2){
    if($ussd_string_exploded[1] == 1):
        $pack = "IKEDC";
    elseif($ussd_string_exploded[1] == 2):
        $pack = "EKEDC";
    elseif($ussd_string_exploded[1] == 3):
        $pack = "KEDCO";
    elseif($ussd_string_exploded[1] == 4):
        $pack = "PHED";
    elseif($ussd_string_exploded[1] == 5):
        $pack = "JED";
    elseif($ussd_string_exploded[1] == 6):
        $pack = "IBEDC";
    elseif($ussd_string_exploded[1] == 7):
        $pack = "KAEDCO";
    elseif($ussd_string_exploded[1] == 8):
        $pack = "AEDC";
    endif;

    $response = "CON Select package: \n";
    $response .= "1. {$pack} Prepaid \n";
    $response .= "2. {$pack} Postpaid \n";
    $response .= "0. Main Menu" ;
}
elseif($ussd_string_exploded[0] == 4 && $level == 3){
    $response = "CON Enter meter no.: \n";
    $response .= "0. Main Menu" ;
}
elseif($ussd_string_exploded[0] == 4 && $level == 4){
    $response = "CON Enter amount:\n( NB: Service charge applies: N100 ) \n";
    $response .= "0. Main Menu" ;
}
elseif($ussd_string_exploded[0] == 4 && $level == 5){
    if($ussd_string_exploded[1] == 1):
        if($ussd_string_exploded[2] == 1):
            $pack = "IKEDC Prepaid";
            $short_code = "ikeja-electric";
            $acct_type = "prepaid";
        else:
            $pack = "IKEDC Postpaid";
            $short_code = "ikeja-electric";
            $acct_type = "postpaid";
        endif;
    elseif($ussd_string_exploded[1] == 2):
        if($ussd_string_exploded[2] == 1):
            $pack = "EKEDC repaid";
            $short_code = "eko-electric";
            $acct_type = "prepaid";
        else:
            $pack = "EKEDC Postpaid";
            $short_code = "eko-electric";
            $acct_type = "postpaid";
        endif;
    elseif($ussd_string_exploded[1] == 3):
        if($ussd_string_exploded[2] == 1):
            $pack = "KEDCO Prepaid";
            $short_code = "kano-electric";
            $acct_type = "prepaid";
        else:
            $pack = "KEDCO Postpaid";
            $short_code = "kano-electric";
            $acct_type = "postpaid";
        endif;
    elseif($ussd_string_exploded[1] == 4):
        if($ussd_string_exploded[2] == 1):
            $pack = "PHED Prepaid";
            $short_code = "portharcourt-electric";
            $acct_type = "prepaid";
        else:
            $pack = "PHED Postpaid";
            $short_code = "portharcourt-electric";
            $acct_type = "postpaid";
        endif;
    elseif($ussd_string_exploded[1] == 5):
        if($ussd_string_exploded[2] == 1):
            $pack = "JED Prepaid";
            $short_code = "jos-electric";
            $acct_type = "prepaid";
        else:
            $pack = "JED Postpaid";
            $short_code = "jos-electric";
            $acct_type = "postpaid";
        endif;
    elseif($ussd_string_exploded[1] == 6):
        if($ussd_string_exploded[2] == 1):
            $pack = "IBEDC Prepaid";
            $short_code = "ibadan-electric";
            $acct_type = "prepaid";
        else:
            $pack = "IBEDC Postpaid";
            $short_code = "ibadan-electric";
            $acct_type = "postpaid";
        endif;
    elseif($ussd_string_exploded[1] == 7):
        if($ussd_string_exploded[2] == 1):
            $pack = "KAEDCO Prepaid";
            $short_code = "kaduna-electric";
            $acct_type = "prepaid";
        else:
            $pack = "KAEDCO Postpaid";
            $short_code = "kaduna-electric";
            $acct_type = "postpaid";
        endif;
    elseif($ussd_string_exploded[1] == 8):
        if($ussd_string_exploded[2] == 1):
            $pack = "AEDC Prepaid";
            $short_code = "abuja-electric";
            $acct_type = "prepaid";
        else:
            $pack = "AEDC Postpaid";
            $short_code = "abuja-electric";
            $acct_type = "postpaid";
        endif;
    endif;
    // ikeja-electric, 1111111111111, prepaid
    $power = $biller_list->VerifyPower($short_code, $ussd_string_exploded[3], $acct_type);
    // $power = $biller_list->VerifyPower("ikeja-electric", "1111111111111", "prepaid");
    $powerList = json_decode($power, true);
    $cus_name = $powerList['content']['Customer_Name'];
    $cus_addr = $powerList['content']['Address'];
    
    $response = "CON Disco : {$pack} \n";
    $response .= "Cus. Info: {$cus_name}, {$cus_addr} \n";
    $response .= "Select Payment Type\n";
    $response .= "1. Bank USSD \n";
    $response .= "2. Wallet \n";
    $response .= "3. Debit Card \n";
    $response .= "4. New Debit Card \n";
    $response .= "0. Main Menu \n";
}
elseif( $ussd_string_exploded[0] == 4 && $level == 6 && $ussd_string_exploded[5] == "1"){
    $response = "CON Select your bank \n";
    $response .= "1. First Bank of Nigeria \n";
    $response .= "2. Zenith Bank \n";
    $response .= "3. Standard Chartered Bank \n";
    $response .= "4. Fidelity Bank \n";
    $response .= "5. Unity Bank \n";
    $response .= "6. JAIZ Bank \n";
    $response .= "7. Ecobank Plc \n";
    $response .= "00. Next";
}
elseif( $ussd_string_exploded[0] == 4 && $level == 7 && $ussd_string_exploded[6] != "00" ){
    // $response = "END You selected bank in level 5 \n";
    $phoneNumber = preg_replace('/^0/','+234', $rootNumber);
    $amtPayingInit = $ussd_string_exploded[4] + 100;
    $amtPaying = ".$amtPayingInit.";
    switch($ussd_string_exploded[5]){
        case "1": //firstbank
            $bankCode = "137";
            break;
        case "2": //Zenith Bank
            $bankCode = "141";
            break;
        case "3": //Standard Chartered Bank
            $bankCode = "142";
            break;
        case "4": //Fidelity Bank
            $bankCode = "144";
            break;
        case "5": //Unity Bank
            $bankCode = "146";
            break;
        case "6": //JAIZ Bank
            $bankCode = "151";
            break;
        default: //Ecobank Plc
            $bankCode = "152";
    }
    if($ussd_string_exploded[1] == 1):
        if($ussd_string_exploded[2] == 1):
            $pack = "IKEDC Prepaid";
            $short_code = "ikeja-electric";
            $acct_type = "prepaid";
        else:
            $pack = "IKEDC Postpaid";
            $short_code = "ikeja-electric";
            $acct_type = "postpaid";
        endif;
    elseif($ussd_string_exploded[1] == 2):
        if($ussd_string_exploded[2] == 1):
            $pack = "EKEDC – Eko Electricity Prepaid";
            $short_code = "eko-electric";
            $acct_type = "prepaid";
        else:
            $pack = "EKEDC – Eko Electricity Postpaid";
            $short_code = "eko-electric";
            $acct_type = "postpaid";
        endif;
    elseif($ussd_string_exploded[1] == 3):
        if($ussd_string_exploded[2] == 1):
            $pack = "KEDCO – Kano Electricity Prepaid";
            $short_code = "kano-electric";
            $acct_type = "prepaid";
        else:
            $pack = "KEDCO – Kano Electricity Postpaid";
            $short_code = "kano-electric";
            $acct_type = "postpaid";
        endif;
    elseif($ussd_string_exploded[1] == 4):
        if($ussd_string_exploded[2] == 1):
            $pack = "PHED – Port Harcourt Electric Prepaid";
            $short_code = "portharcourt-electric";
            $acct_type = "prepaid";
        else:
            $pack = "PHED – Port Harcourt Electric Postpaid";
            $short_code = "portharcourt-electric";
            $acct_type = "postpaid";
        endif;
    elseif($ussd_string_exploded[1] == 5):
        if($ussd_string_exploded[2] == 1):
            $pack = "JED – Jos Electricity Prepaid";
            $short_code = "jos-electric";
            $acct_type = "prepaid";
        else:
            $pack = "JED – Jos Electricity Postpaid";
            $short_code = "jos-electric";
            $acct_type = "postpaid";
        endif;
    elseif($ussd_string_exploded[1] == 6):
        if($ussd_string_exploded[2] == 1):
            $pack = "IBEDC – Ibadan Electricity Prepaid";
            $short_code = "ibadan-electric";
            $acct_type = "prepaid";
        else:
            $pack = "IBEDC – Ibadan Electricity Postpaid";
            $short_code = "ibadan-electric";
            $acct_type = "postpaid";
        endif;
    elseif($ussd_string_exploded[1] == 7):
        if($ussd_string_exploded[2] == 1):
            $pack = "KAEDCO – Kaduna Electric Prepaid";
            $short_code = "kaduna-electric";
            $acct_type = "prepaid";
        else:
            $pack = "KAEDCO – Kaduna Electric Postpaid";
            $short_code = "kaduna-electric";
            $acct_type = "postpaid";
        endif;
    elseif($ussd_string_exploded[1] == 8):
        if($ussd_string_exploded[2] == 1):
            $pack = "AEDC – Abuja Electric Prepaid";
            $short_code = "abuja-electric";
            $acct_type = "prepaid";
        else:
            $pack = "AEDC – Abuja Electric Postpaid";
            $short_code = "abuja-electric";
            $acct_type = "postpaid";
        endif;
    endif;
    $amtPaying = "50";
    // $response = "END Problem sending sms, Please dial: {$amtPayingInit}, {$bankCode}, {$phoneNumber} to complete this transaction.";
    //"tx_ref": "MC~power~600~1111111111111~ikeja-electric~prepaid~+2347065738231~871323",
    // $response = "END {$bankCode}, {$pack}, {$short_code}, {$acct_type}, {$phoneNumber}, {$amtPaying}";
    $whatToDo = "power~". $amtPaying . '~' . $ussd_string_exploded[3] . '~' . $short_code .'~'. $acct_type .'~'.$phoneNumber.'~'. rand(000000, 999999);
    // $response = "END {$whatToDo}";
    $result = $init->MakePayment($amtPaying, $bankCode, $phoneNumber, $whatToDo);
    $rest = json_decode($result, TRUE);
    if($rest["status"] == "success"){
        $content = $rest["meta"]["authorization"]["note"] .". Your bank code is: ". $bankCode;
        $ATsms = new AfricasTalkingCustom();
        $result = $ATsms->SendSms($phoneNumber, $content);
        $res = json_encode($result["data"]);
        $final_res = json_decode($res, true);
        if($final_res["SMSMessageData"]["Recipients"][0]["status"] == "Success"){
            $response = "END To complete this transaction, please dial: ". $rest["meta"]["authorization"]["note"]  . "\nYour bankCode is {$bankCode}.";
        }else{
            $response = "END Problem sending sms, Please dial: " . $rest["meta"]["authorization"]["note"] . "to complete this transaction. Your bank code is : {$bankCode}";
        }
    }else{
        $response = "END Error initiating transaction, please try again!";
    }
}
elseif( $ussd_string_exploded[0] == 4 && $level == 7 && $ussd_string_exploded[6] == "00" ){
    $response = "CON Select your bank \n";
    $response .= "1. Stanbic IBTC Bank \n";
    $response .= "2. Diamond Bank \n";
    $response .= "3. Heritage \n";
    $response .= "4. GTBank Plc \n";
    $response .= "5. Union Bank \n";
    $response .= "6. Sterling Bank \n";
    $response .= "7. Skye Bank \n";
    $response .= "00. Next";
}
elseif( $ussd_string_exploded[0] == 4 && $level == 8 && $ussd_string_exploded[7] != "00" ){
    // $response = "END You selected bank in level 5 \n";
    $destination_no = $ussd_string_exploded[3];
    $phoneNumber = preg_replace('/^0/','+234', $rootNumber);
    $amtPayingInit = (int)$ussd_string_exploded[4] + 100;
    $amtPaying = ".$amtPayingInit.";
    switch($ussd_string_exploded[5]){
        case "1": //Stanbic IBTC Bank
            $bankCode = "158";
            break;
        case "2": //Diamond Bank
            $bankCode = "170";
            break;
        case "3": //Heritage
            $bankCode = "175";
            break;
        case "4": //GTBank Plc
            $bankCode = "177";
            break;
        case "5": //Union Bank
            $bankCode = "178";
            break;
        case "6": //Sterling Bank
            $bankCode = "179";
            break;
        default: //Skye Bank
            $bankCode = "180";
    }
    if($ussd_string_exploded[1] == 1):
        if($ussd_string_exploded[2] == 1):
            $pack = "IKEDC Prepaid";
            $short_code = "ikeja-electric";
            $acct_type = "prepaid";
        else:
            $pack = "IKEDC Postpaid";
            $short_code = "ikeja-electric";
            $acct_type = "postpaid";
        endif;
    elseif($ussd_string_exploded[1] == 2):
        if($ussd_string_exploded[2] == 1):
            $pack = "EKEDC – Eko Electricity Prepaid";
            $short_code = "eko-electric";
            $acct_type = "prepaid";
        else:
            $pack = "EKEDC – Eko Electricity Postpaid";
            $short_code = "eko-electric";
            $acct_type = "postpaid";
        endif;
    elseif($ussd_string_exploded[1] == 3):
        if($ussd_string_exploded[2] == 1):
            $pack = "KEDCO – Kano Electricity Prepaid";
            $short_code = "kano-electric";
            $acct_type = "prepaid";
        else:
            $pack = "KEDCO – Kano Electricity Postpaid";
            $short_code = "kano-electric";
            $acct_type = "postpaid";
        endif;
    elseif($ussd_string_exploded[1] == 4):
        if($ussd_string_exploded[2] == 1):
            $pack = "PHED – Port Harcourt Electric Prepaid";
            $short_code = "portharcourt-electric";
            $acct_type = "prepaid";
        else:
            $pack = "PHED – Port Harcourt Electric Postpaid";
            $short_code = "portharcourt-electric";
            $acct_type = "postpaid";
        endif;
    elseif($ussd_string_exploded[1] == 5):
        if($ussd_string_exploded[2] == 1):
            $pack = "JED – Jos Electricity Prepaid";
            $short_code = "jos-electric";
            $acct_type = "prepaid";
        else:
            $pack = "JED – Jos Electricity Postpaid";
            $short_code = "jos-electric";
            $acct_type = "postpaid";
        endif;
    elseif($ussd_string_exploded[1] == 6):
        if($ussd_string_exploded[2] == 1):
            $pack = "IBEDC – Ibadan Electricity Prepaid";
            $short_code = "ibadan-electric";
            $acct_type = "prepaid";
        else:
            $pack = "IBEDC – Ibadan Electricity Postpaid";
            $short_code = "ibadan-electric";
            $acct_type = "postpaid";
        endif;
    elseif($ussd_string_exploded[1] == 7):
        if($ussd_string_exploded[2] == 1):
            $pack = "KAEDCO – Kaduna Electric Prepaid";
            $short_code = "kaduna-electric";
            $acct_type = "prepaid";
        else:
            $pack = "KAEDCO – Kaduna Electric Postpaid";
            $short_code = "kaduna-electric";
            $acct_type = "postpaid";
        endif;
    elseif($ussd_string_exploded[1] == 8):
        if($ussd_string_exploded[2] == 1):
            $pack = "AEDC – Abuja Electric Prepaid";
            $short_code = "abuja-electric";
            $acct_type = "prepaid";
        else:
            $pack = "AEDC – Abuja Electric Postpaid";
            $short_code = "abuja-electric";
            $acct_type = "postpaid";
        endif;
    endif;
    $amtPaying = "50";
    // $response = "END Problem sending sms, Please dial: {$amtPayingInit}, {$bankCode}, {$phoneNumber} to complete this transaction.";
    //"tx_ref": "MC~power~600~1111111111111~ikeja-electric~prepaid~+2347065738231~871323",
    // $response = "END {$bankCode}, {$pack}, {$short_code}, {$acct_type}, {$phoneNumber}, {$amtPaying}";
    $whatToDo = "power~". $amtPaying . '~' . $ussd_string_exploded[3] . '~' . $short_code .'~'. $acct_type .'~'.$phoneNumber.'~'. rand(000000, 999999);
    // $response = "END {$whatToDo}";
    $result = $init->MakePayment($amtPaying, $bankCode, $phoneNumber, $whatToDo);
    $rest = json_decode($result, TRUE);
    if($rest["status"] == "success"){
        $content = $rest["meta"]["authorization"]["note"] .". Your bank code is: ". $bankCode;
        $ATsms = new AfricasTalkingCustom();
        $result = $ATsms->SendSms($phoneNumber, $content);
        $res = json_encode($result["data"]);
        $final_res = json_decode($res, true);
        if($final_res["SMSMessageData"]["Recipients"][0]["status"] == "Success"){
            $response = "END To complete this transaction, please dial: ". $rest["meta"]["authorization"]["note"] . "\nYour bankCode is {$bankCode}.";
        }else{
            $response = "END Problem sending sms, Please dial: " . $rest["meta"]["authorization"]["note"] . "to complete this transaction. Your bank code is : {$bankCode}";
        }
    }else{
        $response = "END Error initiating transaction, please try again!";
    }
}
elseif($ussd_string_exploded[0] == 4 && $level == 8 && $ussd_string_exploded[7] == "00"){
    $response = "CON Select your bank\n";
    $response .= "1. Keystone Bank \n";
    $response .= "2. Fidelity  \n";
    $response .= "3. First City Monument Bank \n";
    $response .= "4. United Bank for Africa \n";
    $response .= "5. Access Bank \n";
    $response .= "6. Wema Bank \n";
}
elseif($ussd_string_exploded[0] == 4 && $level == 9 && $ussd_string_exploded[8] != ""){
    $amtPayingInit = (int)$ussd_string_exploded[4] + 100;
    $amtPaying = ".$amtPayingInit.";
    switch($ussd_string_exploded[6]){
        case "1": //Keystone Bank
            $bankCode = "181";
            break;
        case "2": //Fidelity
            $bankCode = "144";
            break;
        case "3": //First City Monument Bank
            $bankCode = "186";
            break;
        case "4": //United Bank for Africa
            $bankCode = "190";
            break;
        case "5": //Access Bank
            $bankCode = "191";
            break;
        default: //Wema Bank
            $bankCode = "168";
    }
    $destination_no = $ussd_string_exploded[3];
    $phoneNumber = preg_replace('/^0/','+234', $rootNumber);
    if($ussd_string_exploded[1] == 1):
        if($ussd_string_exploded[2] == 1):
            $pack = "IKEDC Prepaid";
            $short_code = "ikeja-electric";
            $acct_type = "prepaid";
        else:
            $pack = "IKEDC Postpaid";
            $short_code = "ikeja-electric";
            $acct_type = "postpaid";
        endif;
    elseif($ussd_string_exploded[1] == 2):
        if($ussd_string_exploded[2] == 1):
            $pack = "EKEDC – Eko Electricity Prepaid";
            $short_code = "eko-electric";
            $acct_type = "prepaid";
        else:
            $pack = "EKEDC – Eko Electricity Postpaid";
            $short_code = "eko-electric";
            $acct_type = "postpaid";
        endif;
    elseif($ussd_string_exploded[1] == 3):
        if($ussd_string_exploded[2] == 1):
            $pack = "KEDCO – Kano Electricity Prepaid";
            $short_code = "kano-electric";
            $acct_type = "prepaid";
        else:
            $pack = "KEDCO – Kano Electricity Postpaid";
            $short_code = "kano-electric";
            $acct_type = "postpaid";
        endif;
    elseif($ussd_string_exploded[1] == 4):
        if($ussd_string_exploded[2] == 1):
            $pack = "PHED – Port Harcourt Electric Prepaid";
            $short_code = "portharcourt-electric";
            $acct_type = "prepaid";
        else:
            $pack = "PHED – Port Harcourt Electric Postpaid";
            $short_code = "portharcourt-electric";
            $acct_type = "postpaid";
        endif;
    elseif($ussd_string_exploded[1] == 5):
        if($ussd_string_exploded[2] == 1):
            $pack = "JED – Jos Electricity Prepaid";
            $short_code = "jos-electric";
            $acct_type = "prepaid";
        else:
            $pack = "JED – Jos Electricity Postpaid";
            $short_code = "jos-electric";
            $acct_type = "postpaid";
        endif;
    elseif($ussd_string_exploded[1] == 6):
        if($ussd_string_exploded[2] == 1):
            $pack = "IBEDC – Ibadan Electricity Prepaid";
            $short_code = "ibadan-electric";
            $acct_type = "prepaid";
        else:
            $pack = "IBEDC – Ibadan Electricity Postpaid";
            $short_code = "ibadan-electric";
            $acct_type = "postpaid";
        endif;
    elseif($ussd_string_exploded[1] == 7):
        if($ussd_string_exploded[2] == 1):
            $pack = "KAEDCO – Kaduna Electric Prepaid";
            $short_code = "kaduna-electric";
            $acct_type = "prepaid";
        else:
            $pack = "KAEDCO – Kaduna Electric Postpaid";
            $short_code = "kaduna-electric";
            $acct_type = "postpaid";
        endif;
    elseif($ussd_string_exploded[1] == 8):
        if($ussd_string_exploded[2] == 1):
            $pack = "AEDC – Abuja Electric Prepaid";
            $short_code = "abuja-electric";
            $acct_type = "prepaid";
        else:
            $pack = "AEDC – Abuja Electric Postpaid";
            $short_code = "abuja-electric";
            $acct_type = "postpaid";
        endif;
    endif;
    $amtPaying = "50";
    // $response = "END Problem sending sms, Please dial: {$amtPayingInit}, {$bankCode}, {$phoneNumber} to complete this transaction.";
    //"tx_ref": "MC~power~600~1111111111111~ikeja-electric~prepaid~+2347065738231~871323",
    // $response = "END {$bankCode}, {$pack}, {$short_code}, {$acct_type}, {$phoneNumber}, {$amtPaying}";
    $whatToDo = "power~". $amtPaying . '~' . $ussd_string_exploded[3] . '~' . $short_code .'~'. $acct_type .'~'.$phoneNumber.'~'. rand(000000, 999999);
    // $response = "END {$whatToDo}";
    $result = $init->MakePayment($amtPaying, $bankCode, $phoneNumber, $whatToDo);
    $rest = json_decode($result, TRUE);
    if($rest["status"] == "success"){
        $content = $rest["meta"]["authorization"]["note"] .". Your bank code is: ". $bankCode;
        $ATsms = new AfricasTalkingCustom();
        $result = $ATsms->SendSms($phoneNumber, $content);
        $res = json_encode($result["data"]);
        $final_res = json_decode($res, true);
        if($final_res["SMSMessageData"]["Recipients"][0]["status"] == "Success"){
            $response = "END To complete this transaction, please dial: ". $rest["meta"]["authorization"]["note"] . "\nYour bankCode is {$bankCode}.";
        }else{
            $response = "END Problem sending sms, Please dial: " . $rest["meta"]["authorization"]["note"] . "to complete this transaction. Your bank code is : {$bankCode}";
        }
    }else{
        $response = "END Error initiating transaction, please try again!";
    }
}
elseif( $ussd_string_exploded[0] == 4  && $ussd_string_exploded[5] == "2" && $level == 6){
    $country_code = "+234";
    $rootNumber = preg_replace("/^\+?{$country_code}/", '',$rootNumber);
    $rootNumber = "0".$rootNumber;

    $getUserWallet = $wallet->getUser($rootNumber);
    $wallet = json_decode($getUserWallet, true);
    $wallet_acct_bal = $wallet['account_balance'];
    $wallet_pin = $wallet['pin'];
    $wallet_status = $wallet['status'];

    $response = "CON Your wallet balance: {$wallet_acct_bal}\n";
    $response .= "1. Proceed \n";
    $response .= "2. Cancel \n";
    $response .= "0. Main Menu \n";
}
//Final payment through walleting
elseif($ussd_string_exploded[0] == 4 && $ussd_string_exploded[5] == "2" && $level == 7){
    $getUserWallet = $wallet->getUser($rootNumber);
    $wallet = json_decode($getUserWallet, true);
    $wallet_acct_bal = $wallet['account_balance'];
    $wallet_pin = $wallet['pin'];
    $wallet_status = $wallet['status'];

    $phoneNumber = preg_replace('/^0/','+234', $rootNumber);
    $amtPayingInit = $ussd_string_exploded[4] + 100;
    $amtPaying = ".$amtPayingInit.";
    switch($ussd_string_exploded[5]){
        case "1": //firstbank
            $bankCode = "137";
            break;
        case "2": //Zenith Bank
            $bankCode = "141";
            break;
        case "3": //Standard Chartered Bank
            $bankCode = "142";
            break;
        case "4": //Fidelity Bank
            $bankCode = "144";
            break;
        case "5": //Unity Bank
            $bankCode = "146";
            break;
        case "6": //JAIZ Bank
            $bankCode = "151";
            break;
        default: //Ecobank Plc
            $bankCode = "152";
    }
    if($ussd_string_exploded[1] == 1):
        if($ussd_string_exploded[2] == 1):
            $pack = "IKEDC Prepaid";
            $short_code = "ikeja-electric";
            $acct_type = "prepaid";
        else:
            $pack = "IKEDC Postpaid";
            $short_code = "ikeja-electric";
            $acct_type = "postpaid";
        endif;
    elseif($ussd_string_exploded[1] == 2):
        if($ussd_string_exploded[2] == 1):
            $pack = "EKEDC – Eko Electricity Prepaid";
            $short_code = "eko-electric";
            $acct_type = "prepaid";
        else:
            $pack = "EKEDC – Eko Electricity Postpaid";
            $short_code = "eko-electric";
            $acct_type = "postpaid";
        endif;
    elseif($ussd_string_exploded[1] == 3):
        if($ussd_string_exploded[2] == 1):
            $pack = "KEDCO – Kano Electricity Prepaid";
            $short_code = "kano-electric";
            $acct_type = "prepaid";
        else:
            $pack = "KEDCO – Kano Electricity Postpaid";
            $short_code = "kano-electric";
            $acct_type = "postpaid";
        endif;
    elseif($ussd_string_exploded[1] == 4):
        if($ussd_string_exploded[2] == 1):
            $pack = "PHED – Port Harcourt Electric Prepaid";
            $short_code = "portharcourt-electric";
            $acct_type = "prepaid";
        else:
            $pack = "PHED – Port Harcourt Electric Postpaid";
            $short_code = "portharcourt-electric";
            $acct_type = "postpaid";
        endif;
    elseif($ussd_string_exploded[1] == 5):
        if($ussd_string_exploded[2] == 1):
            $pack = "JED – Jos Electricity Prepaid";
            $short_code = "jos-electric";
            $acct_type = "prepaid";
        else:
            $pack = "JED – Jos Electricity Postpaid";
            $short_code = "jos-electric";
            $acct_type = "postpaid";
        endif;
    elseif($ussd_string_exploded[1] == 6):
        if($ussd_string_exploded[2] == 1):
            $pack = "IBEDC – Ibadan Electricity Prepaid";
            $short_code = "ibadan-electric";
            $acct_type = "prepaid";
        else:
            $pack = "IBEDC – Ibadan Electricity Postpaid";
            $short_code = "ibadan-electric";
            $acct_type = "postpaid";
        endif;
    elseif($ussd_string_exploded[1] == 7):
        if($ussd_string_exploded[2] == 1):
            $pack = "KAEDCO – Kaduna Electric Prepaid";
            $short_code = "kaduna-electric";
            $acct_type = "prepaid";
        else:
            $pack = "KAEDCO – Kaduna Electric Postpaid";
            $short_code = "kaduna-electric";
            $acct_type = "postpaid";
        endif;
    elseif($ussd_string_exploded[1] == 8):
        if($ussd_string_exploded[2] == 1):
            $pack = "AEDC – Abuja Electric Prepaid";
            $short_code = "abuja-electric";
            $acct_type = "prepaid";
        else:
            $pack = "AEDC – Abuja Electric Postpaid";
            $short_code = "abuja-electric";
            $acct_type = "postpaid";
        endif;
    endif;
    $amtPaying = "600";
//    $whatToDo = "power~". $amtPaying . '~' . $ussd_string_exploded[3] . '~' . $short_code .'~'. $acct_type .'~'.$phoneNumber.'~'. rand(000000, 999999);
    $ref = mt_rand(0000000, 9999999);
    if($amtPaying < $wallet_acct_bal){
        $buyTruWallet = $activity->BuyCable($ref, $short_code, $ussd_string_exploded[3], $acct_type, $amtPaying, $rootNumber);
        $result = json_decode($buyTruWallet, true);
        $tokenated = $result['purchased_code'];
//        $message = "Your {$tokenated}\nAmount: N{$total_amount},\nFrom: {$serviceID}.\n\n-Thanks for choosing WOLID.";
        if($result['code'] === "000"){
            $response = "END Successful.\nYour Token: {$tokenated} - {$short_code}.\n\nThanks for choosing WOLID.";
        }else{
            $response = "END Could not process your request, please contact customer care.\n\n-WOLID.";
        }
    }else{
        $response = "END You do not have enough balance in your wallet. Fund your wallet or buy using through bank.\n\n-WOLID";
    }
//    $response = "END You are about to purchase of electricity tariff. Please wait while you are being redirected to make payment.\n\n-WOLID";
}
///////////////////////////////////////////////////////////
////////////// End of electricity bill ///////////////////
/////////////////////////////////////////////////////////
///


////////////////////////////////////////////////
////////////// Start of GoTV //////////////////
//////////////////////////////////////////////
elseif($text == "5") {

    
    $getUserWallet = $wallet->getUser($rootNumber);
    $wallet = json_decode($getUserWallet, true);
    $wallet_acct_bal = $wallet['account_balance'];
    $wallet_pin = $wallet['pin'];
    $wallet_status = $wallet['status']; 

    
    $response  = "CON GoTV Recharge: \n";
    $response .= "1. GOtv Lite N410  \n";
    $response .= "2. GOtv Max N3,600 \n";
    $response .= "3. GOtv Jolli N2,460 \n";
    $response .= "4. GOtv Jinja N1,640 \n";
    $response .= "5. GOtv Lite (3 Months) N1,080 \n";
    $response .= "6. GOtv Lite (1 Year) N3,180";
}
elseif($ussd_string_exploded[0] == 5 && $level == 2){
    $response = "CON Enter GOTV IUC No. \n";
    $response .= "0 Main Menu"; 
}
elseif($ussd_string_exploded[0] == 5 && $level == 3){
    $verifyDSTV = $biller_list->VerifyCable('gotv', $ussd_string_exploded[2]);
    $verifydata = json_decode($verifyDSTV, true);

    if($ussd_string_exploded[1] == "1"):
        $pack = "GOtv Lite N410";
        $variation_code = "gotv-lite";
    elseif($ussd_string_exploded[1] == "2"):
        $pack = "GOtv Max N3,600";
        $variation_code = "gotv-max";
    elseif($ussd_string_exploded[1] == "3"):
        $pack = "GOtv Jolli N2,460";
        $variation_code = "gotv-jolli";
    elseif($ussd_string_exploded[1] == "4"):
        $pack = "GOtv Jinja N1,640";
        $variation_code = "gotv-jinja";
    elseif($ussd_string_exploded[1] == "5"):
        $pack = "GOtv Lite (3 Months) N1,080";
        $variation_code = "gotv-lite-3months";
    elseif($ussd_string_exploded[1] == "6"):
        $pack = "GOtv Lite (1 Year) N3,180";
        $variation_code = "gotv-lite-1year";
    endif;
    $response = "CON Confirm : GoTV - ".$pack." \n";
    $response .= "Name: {$verifydata["content"]["Customer_Name"]}\n";
    $response .= "Select Payment Type \n";
    $response .= "1. Bank USSD \n";
    $response .= "2. Wallet \n";
    $response .= "3. Debit Card \n";
    $response .= "4. New Debit Card \n";
    $response .= "0. Main Menu \n";
}
elseif( $ussd_string_exploded[0] == 5 && $level == 4){
    $response = "CON Select your bank \n";
    $response .= "1. First Bank of Nigeria \n";
    $response .= "2. Zenith Bank \n";
    $response .= "3. Standard Chartered Bank \n";
    $response .= "4. Fidelity Bank \n";
    $response .= "5. Unity Bank \n";
    $response .= "6. JAIZ Bank \n";
    $response .= "7. Ecobank Plc \n";
    $response .= "00. Next";
}
elseif( $ussd_string_exploded[0] == 5 && $level == 5 && $ussd_string_exploded[4] != "00" ){
    // $response = "END You selected bank in level 5 \n";
    $phoneNumber = preg_replace('/^0/','+234', $rootNumber);
    switch($ussd_string_exploded[1]){
        case "1":
            $amtPaying = "410";
            $variation_code = "gotv-lite";
            break;
        case "2":
            $amtPaying = "3600";
            $variation_code = "gotv-max";
            break;
        case "3":
            $amtPaying = "2460";
            $variation_code = "gotv-jolli";
            break;
        case "4":
            $amtPaying = "1640";
            $variation_code = "gotv-jinja";
            break;
        case "5":
            $amtPaying = "1080";
            $variation_code = "gotv-lite-3months";
            break;
        default:
            $amtPaying = "3180";
            $variation_code = "gotv-lite-1year";
    }
    switch($ussd_string_exploded[4]){
        case "1": //firstbank
            $bankCode = "137";
            break;
        case "2": //Zenith Bank
            $bankCode = "141";
            break;
        case "3": //Standard Chartered Bank
            $bankCode = "142";
            break;
        case "4": //Fidelity Bank
            $bankCode = "144";
            break;
        case "5": //Unity Bank
            $bankCode = "146";
            break;
        case "6": //JAIZ Bank
            $bankCode = "151";
            break;
        default: //Ecobank Plc
            $bankCode = "152";
    }
    $request_id = rand(000000, 999999);
    $billersCode = $ussd_string_exploded[2];
    $amtPaying = "50";
    $whatToDo = "gotv~".$variation_code .'~'. $amtPaying . '~' . $billersCode . '~' . $phoneNumber .'~'. rand(000000, 999999);
    // $response = "END { $whatToDo }";
    $result = $init->MakePayment($amtPaying, $bankCode, $phoneNumber, $whatToDo);
    $rest = json_decode($result, TRUE);
    if($rest["status"] == "success"){
        $content = $rest["meta"]["authorization"]["note"]  . "\nYour bankCode is {$bankCode}.";
        $ATsms = new AfricasTalkingCustom();
        $result = $ATsms->SendSms($phoneNumber, $content);
        $res = json_encode($result["data"]);
        $final_res = json_decode($res, true);
        if($final_res["SMSMessageData"]["Recipients"][0]["status"] == "Success"){
            $response = "END To complete this transaction, please dial: ". $rest["meta"]["authorization"]["note"]. "\nYour bankCode is {$bankCode}.";
        }else{
            $response = "END Problem sending sms, Please dial: " . $rest["meta"]["authorization"]["note"] . "to complete this transaction.";
        }
    }else{
        $response = "END Error initiating transaction, please try again!";
    }
}
elseif( $ussd_string_exploded[0] == 5 && $level == 5 && $ussd_string_exploded[4] == "00" ){
    $response = "CON Select your bank \n";
    $response .= "1. Stanbic IBTC Bank \n";
    $response .= "2. Diamond Bank \n";
    $response .= "3. Heritage \n";
    $response .= "4. GTBank Plc \n";
    $response .= "5. Union Bank \n";
    $response .= "6. Sterling Bank \n";
    $response .= "7. Skye Bank \n";
    $response .= "00. Next";
}
elseif( $ussd_string_exploded[0] == 5 && $level == 6 && $ussd_string_exploded[5] != "00" ){
    // $response = "END You selected bank in level 5 \n";
    $destination_no = $ussd_string_exploded[3];
    $phoneNumber = preg_replace('/^0/','+234', $rootNumber);
    switch($ussd_string_exploded[1]){
        case "1":
            $amtPaying = "410";
            $variation_code = "gotv-lite";
            break;
        case "2":
            $amtPaying = "3600";
            $variation_code = "gotv-max";
            break;
        case "3":
            $amtPaying = "2460";
            $variation_code = "gotv-jolli";
            break;
        case "4":
            $amtPaying = "1640";
            $variation_code = "gotv-jinja";
            break;
        case "5":
            $amtPaying = "1080";
            $variation_code = "gotv-lite-3months";
            break;
        default:
            $amtPaying = "3180";
            $variation_code = "gotv-lite-1year";
    }
    switch($ussd_string_exploded[5]){
        case "1": //Stanbic IBTC Bank
            $bankCode = "158";
            break;
        case "2": //Diamond Bank
            $bankCode = "170";
            break;
        case "3": //Heritage
            $bankCode = "175";
            break;
        case "4": //GTBank Plc
            $bankCode = "177";
            break;
        case "5": //Union Bank
            $bankCode = "178";
            break;
        case "6": //Sterling Bank
            $bankCode = "179";
            break;
        default: //Skye Bank
            $bankCode = "180";
    }
    $amtPaying = "50";
    $request_id = rand(000000, 999999);
    $billersCode = $ussd_string_exploded[2];
    $whatToDo = "gotv~".$variation_code .'~'. $amtPaying . '~' . $billersCode . '~' . $phoneNumber .'~'. rand(000000, 999999);
    // $response = "END { $whatToDo }";
    $result = $init->MakePayment($amtPaying, $bankCode, $phoneNumber, $whatToDo);
    $rest = json_decode($result, TRUE);
    if($rest["status"] == "success"){
        $content = $rest["meta"]["authorization"]["note"] . "\nYour bankCode is {$bankCode}.";
        $ATsms = new AfricasTalkingCustom();
        $result = $ATsms->SendSms($phoneNumber, $content);
        $res = json_encode($result["data"]);
        $final_res = json_decode($res, true);
        if($final_res["SMSMessageData"]["Recipients"][0]["status"] == "Success"){
            $response = "END To complete this transaction, please dial: ". $rest["meta"]["authorization"]["note"] . "\nYour bankCode is {$bankCode}.";
        }else{
            $response = "END Problem sending sms, Please dial: " . $rest["meta"]["authorization"]["note"] . "to complete this transaction.";
        }
    }else{
        $response = "END Error initiating transaction, please try again!";
    }
}
elseif($ussd_string_exploded[0] == 5 && $level == 6 && $ussd_string_exploded[5] == "00"){
    $response = "CON Select your bank\n";
    $response .= "1. Keystone Bank \n";
    $response .= "2. Fidelity  \n";
    $response .= "3. First City Monument Bank \n";
    $response .= "4. United Bank for Africa \n";
    $response .= "5. Access Bank";
    $response .= "6. Wema Bank \n";
}
elseif($ussd_string_exploded[0] == 5 && $level == 7 && $ussd_string_exploded[6] != ""){
    switch($ussd_string_exploded[1]){
        case "1":
            $amtPaying = "410";
            $variation_code = "gotv-lite";
            break;
        case "2":
            $amtPaying = "3600";
            $variation_code = "gotv-max";
            break;
        case "3":
            $amtPaying = "2460";
            $variation_code = "gotv-jolli";
            break;
        case "4":
            $amtPaying = "1640";
            $variation_code = "gotv-jinja";
            break;
        case "5":
            $amtPaying = "1080";
            $variation_code = "gotv-lite-3months";
            break;
        default:
            $amtPaying = "3180";
            $variation_code = "gotv-lite-1year";
    }
    switch($ussd_string_exploded[6]){
        case "1": //Keystone Bank
            $bankCode = "181";
            break;
        case "2": //Fidelity
            $bankCode = "144";
            break;
        case "3": //First City Monument Bank
            $bankCode = "186";
            break;
        case "4": //United Bank for Africa
            $bankCode = "190";
            break;
        case "5": //Access Bank
            $bankCode = "191";
            break;
        default: //Wema Bank
            $bankCode = "168";
    }
    $destination_no = $ussd_string_exploded[3];
    $phoneNumber = preg_replace('/^0/','+234', $rootNumber);
    
    $amtPaying = "50";
    $request_id = rand(000000, 999999);
    $billersCode = $ussd_string_exploded[2];
    $whatToDo = "gotv~".$variation_code .'~'. $amtPaying . '~' . $billersCode . '~' . $phoneNumber .'~'. rand(000000, 999999);
    // $response = "END { $whatToDo }";

    $result = $init->MakePayment($amtPaying, $bankCode, $phoneNumber, $whatToDo);
    $rest = json_decode($result, TRUE);
    if($rest["status"] == "success"){
        $content = $rest["meta"]["authorization"]["note"] . "\nYour bankCode is {$bankCode}.";
        $ATsms = new AfricasTalkingCustom();
        $result = $ATsms->SendSms($phoneNumber, $content);
        $res = json_encode($result["data"]);
        $final_res = json_decode($res, true);
        if($final_res["SMSMessageData"]["Recipients"][0]["status"] == "Success"){
            $response = "END To complete this transaction, please dial: ". $rest["meta"]["authorization"]["note"] . "\nYour bankCode is {$bankCode}.";
        }else{
            $response = "END Problem sending sms, Please dial: " . $rest["meta"]["authorization"]["note"] . "to complete this transaction.";
        }
    }else{
        $response = "END Error initiating transaction, please try again!";
    }
}
elseif( $ussd_string_exploded[0] == 5 && $level == 4 && $ussd_string_exploded[3] == "2" ){
    $response = "CON Your wallet balance: \n";
    $response .= "1. Proceed \n";
    $response .= "2. Cancel \n";
    $response .= "0. Main Menu \n";
}
//Final payment through walleting
elseif($ussd_string_exploded[0] == 5 && $level == 5 && $ussd_string_exploded[3] == "2"){
    $response = "END You are about to purchase of GoTV tariff. Please wait while you are being redirected to make payment \n";
}
////////////////////////////////////////////////
////////////// End of GoTV ////////////////////
//////////////////////////////////////////////




////////////////////////////////////////////////
////////////// Start of DSTV //////////////////
//////////////////////////////////////////////
elseif($text == "6") {
    
    $getUserWallet = $wallet->getUser($rootNumber);
    $wallet = json_decode($getUserWallet, true);
    $wallet_acct_bal = $wallet['account_balance'];
    $wallet_pin = $wallet['pin'];
    $wallet_status = $wallet['status'];

    
    $response  = "CON Choose DSTV plan \n";
    $response .= "1. Padi N1,850 \n";
    $response .= "2. Yanga N2,565 \n";
    $response .= "3. Confam N4,615 \n";
    $response .= "4. Compact N7900 \n";
    $response .= "5. Premium N18,400 \n";
    $response .= "6. +Compact N12,400 \n";
    $response .= "7. +Confam N7,115 \n";
    $response .= "00. +Next";
}
elseif($ussd_string_exploded[0] == 6 && $level == 2 && $ussd_string_exploded[1] != "00"){
    $response = "CON Enter DSTV IUC No.: \n";
    $response .= "0 Main Menu"; 
}
elseif($ussd_string_exploded[0] == 6 && $level == 3 && $ussd_string_exploded[1] != "00"){
    $verifyDSTV = $biller_list->VerifyCable('dstv', $ussd_string_exploded[2]);
    $verifydata = json_decode($verifyDSTV, true);
    switch($ussd_string_exploded[1]){
        case "1":
            $val = "DStv Padi N1,850";
            break;
        case "2":
            $val = "DStv Yanga N2,565";
            break;
        case "3":
            $val = "Dstv Confam N4,615";
            break;
        case "4":
            $val = "DStv Compact N7900";
            break;
        case "5":
            $val = "DStv Premium N18,400";
            break;
        case "6":
            $val = "DStv Compact Plus N12,400";
            break;
        default:
            $val = "DStv Confam + ExtraView N7,115";
    }

    $response = "CON Confirm : ".$val." \n";
    $response .= "Name: {$verifydata["content"]["Customer_Name"]}\n";
    $response .= "Select Payment Type \n";
    $response .= "1. Bank USSD \n";
    $response .= "2. Wallet \n";
    $response .= "3. Debit Card \n";
    $response .= "4. New Debit Card \n";
    $response .= "0. Main Menu \n";
}
elseif( $ussd_string_exploded[0] == 6 && $level == 4 && $ussd_string_exploded[1] != "00"){
    $response = "CON Select your bank \n";
    $response .= "1. First Bank of Nigeria \n";
    $response .= "2. Zenith Bank \n";
    $response .= "3. Standard Chartered Bank \n";
    $response .= "4. Fidelity Bank \n";
    $response .= "5. Unity Bank \n";
    $response .= "6. JAIZ Bank \n";
    $response .= "7. Ecobank Plc \n";
    $response .= "00. Next";
}
elseif( $ussd_string_exploded[0] == 6 && $level == 5 && $ussd_string_exploded[1] != "00" && $ussd_string_exploded[4] != "00" ){
    // $response = "END You selected bank in level 5 \n";
    $phoneNumber = preg_replace('/^0/','+234', $rootNumber);
    switch($ussd_string_exploded[1]){
        case "1":
            $amtPaying = "1850";
            $variation_code = "dstv-padi";
            break;
        case "2":
            $amtPaying = "2565";
            $variation_code = "dstv-yanga";
            break;
        case "3":
            $amtPaying = "4615";
            $variation_code = "dstv-confirm";
            break;
        case "4":
            $amtPaying = "7900";
            $variation_code = "dstv79";
            break;
        case "5":
            $amtPaying = "18400";
            $variation_code = "dstv3";
            break;
        case "6":
            $amtPaying = "12400";
            $variation_code = "dstv7";
            break;
        default:
            $amtPaying = "7115";
            $variation_code = "confam-extra";
    }
    switch($ussd_string_exploded[4]){
        case "1": //firstbank
            $bankCode = "137";
            break;
        case "2": //Zenith Bank
            $bankCode = "141";
            break;
        case "3": //Standard Chartered Bank
            $bankCode = "142";
            break;
        case "4": //Fidelity Bank
            $bankCode = "144";
            break;
        case "5": //Unity Bank
            $bankCode = "146";
            break;
        case "6": //JAIZ Bank
            $bankCode = "151";
            break;
        default: //Ecobank Plc
            $bankCode = "152";
    }
    $amtPaying = "50";
    $request_id = rand(000000, 999999);
    $billersCode = $ussd_string_exploded[2];
    $whatToDo = "dstv~".$variation_code .'~'. $amtPaying . '~' . $billersCode . '~' . $phoneNumber .'~'. rand(000000, 999999);
    // $response = "END {$whatToDo}";
    $result = $init->MakePayment($amtPaying, $bankCode, $phoneNumber, $whatToDo);
    $rest = json_decode($result, TRUE);
    if($rest["status"] == "success"){
        $content = $rest["meta"]["authorization"]["note"]. "\nYour bank code is: {$bankCode}.";
        $ATsms = new AfricasTalkingCustom();
        $result = $ATsms->SendSms($phoneNumber, $content);
        $res = json_encode($result["data"]);
        $final_res = json_decode($res, true);
        if($final_res["SMSMessageData"]["Recipients"][0]["status"] == "Success"){
            $response = "END To complete this transaction, please dial: ". $rest["meta"]["authorization"]["note"]. "\nYour bank code is: {$bankCode}.";
        }else{
            $response = "END Problem sending sms, Please dial: " . $rest["meta"]["authorization"]["note"] . "to complete this transaction. \nYour bank code is: {$bankCode}.";
        }
    }else{
        $response = "END Error initiating transaction, please try again!";
    }
}
elseif( $ussd_string_exploded[0] == 6 && $level == 5 && $ussd_string_exploded[1] != "00" && $ussd_string_exploded[4] == "00" ){
    $response = "CON Select your bank \n";
    $response .= "1. Stanbic IBTC Bank \n";
    $response .= "2. Diamond Bank \n";
    $response .= "3. Heritage \n";
    $response .= "4. GTBank Plc \n";
    $response .= "5. Union Bank \n";
    $response .= "6. Sterling Bank \n";
    $response .= "7. Skye Bank \n";
    $response .= "00. Next";
}
elseif( $ussd_string_exploded[0] == 6 && $level == 6 && $ussd_string_exploded[1] != "00" && $ussd_string_exploded[5] != "00" ){
    // $response = "END You selected bank in level 5 \n";
    $phoneNumber = preg_replace('/^0/','+234', $rootNumber);
    switch($ussd_string_exploded[1]){
        case "1":
            $amtPaying = "1850";
            $variation_code = "dstv-padi";
            break;
        case "2":
            $amtPaying = "2565";
            $variation_code = "dstv-yanga";
            break;
        case "3":
            $amtPaying = "4615";
            $variation_code = "dstv-confirm";
            break;
        case "4":
            $amtPaying = "7900";
            $variation_code = "dstv79";
            break;
        case "5":
            $amtPaying = "18400";
            $variation_code = "dstv3";
            break;
        case "6":
            $amtPaying = "12400";
            $variation_code = "dstv7";
            break;
        default:
            $amtPaying = "7115";
            $variation_code = "confam-extra";
    }
    switch($ussd_string_exploded[5]){
        case "1": //Stanbic IBTC Bank
            $bankCode = "158";
            break;
        case "2": //Diamond Bank
            $bankCode = "170";
            break;
        case "3": //Heritage
            $bankCode = "175";
            break;
        case "4": //GTBank Plc
            $bankCode = "177";
            break;
        case "5": //Union Bank
            $bankCode = "178";
            break;
        case "6": //Sterling Bank
            $bankCode = "179";
            break;
        default: //Skye Bank
            $bankCode = "180";
    }
    $amtPaying = "50";
    $billersCode = $ussd_string_exploded[2];
    $whatToDo = "dstv~".$variation_code .'~'. $amtPaying . '~' . $billersCode . '~' . $phoneNumber .'~'. rand(000000, 999999);
    // $response = "END {$whatToDo}";
    $result = $init->MakePayment($amtPaying, $bankCode, $phoneNumber, $whatToDo);
    $rest = json_decode($result, TRUE);
    if($rest["status"] == "success"){
        $content = $rest["meta"]["authorization"]["note"]. "\nYour bank code is: {$bankCode}.";
        $ATsms = new AfricasTalkingCustom();
        $result = $ATsms->SendSms($phoneNumber, $content);
        $res = json_encode($result["data"]);
        $final_res = json_decode($res, true);
        if($final_res["SMSMessageData"]["Recipients"][0]["status"] == "Success"){
            $response = "END To complete this transaction, please dial: ". $rest["meta"]["authorization"]["note"]. "\nYour bank code is: {$bankCode}.";
        }else{
            $response = "END Problem sending sms, Please dial: " . $rest["meta"]["authorization"]["note"] . "to complete this transaction. \nYour bank code is: {$bankCode}.";
        }
    }else{
        $response = "END Error initiating transaction, please try again!";
    }
}
elseif($ussd_string_exploded[0] == 6 && $level == 6 && $ussd_string_exploded[1] != "00" && $ussd_string_exploded[5] == "00"){
    $response = "CON Select your bank\n";
    $response .= "1. Keystone Bank \n";
    $response .= "2. Fidelity  \n";
    $response .= "3. First City Monument Bank \n";
    $response .= "4. United Bank for Africa \n";
    $response .= "5. Access Bank \n";
    $response .= "6. Wema Bank \n";
}
elseif($ussd_string_exploded[0] == 6 && $ussd_string_exploded[1] != "00" && $level == 7){
    switch($ussd_string_exploded[1]){
        case "1":
            $amtPaying = "1850";
            $variation_code = "dstv-padi";
            break;
        case "2":
            $amtPaying = "2565";
            $variation_code = "dstv-yanga";
            break;
        case "3":
            $amtPaying = "4615";
            $variation_code = "dstv-confirm";
            break;
        case "4":
            $amtPaying = "7900";
            $variation_code = "dstv79";
            break;
        case "5":
            $amtPaying = "18400";
            $variation_code = "dstv3";
            break;
        case "6":
            $amtPaying = "12400";
            $variation_code = "dstv7";
            break;
        default:
            $amtPaying = "7115";
            $variation_code = "confam-extra";
    }
    switch($ussd_string_exploded[6]){
        case "1": //Keystone Bank
            $bankCode = "181";
            break;
        case "2": //Fidelity
            $bankCode = "144";
            break;
        case "3": //First City Monument Bank
            $bankCode = "186";
            break;
        case "4": //United Bank for Africa
            $bankCode = "190";
            break;
        case "5": //Access Bank
            $bankCode = "191";
            break;
        default: //Wema Bank
            $bankCode = "168";
    }
    $amtPaying = "50";
    $billersCode = $ussd_string_exploded[2];
    $phoneNumber = preg_replace('/^0/','+234', $rootNumber);
    $whatToDo = "dstv~".$variation_code .'~'. $amtPaying . '~' . $billersCode . '~' . $phoneNumber .'~'. rand(000000, 999999);
//    $response = "END {$whatToDo}";
     $result = $init->MakePayment($amtPaying, $bankCode, $phoneNumber, $whatToDo);
     $rest = json_decode($result, TRUE);
     if($rest["status"] == "success"){
         $content = $rest["meta"]["authorization"]["note"]. "\nYour bank code is: {$bankCode}.";
         $ATsms = new AfricasTalkingCustom();
         $result = $ATsms->SendSms($phoneNumber, $content);
         $res = json_encode($result["data"]);
         $final_res = json_decode($res, true);
         if($final_res["SMSMessageData"]["Recipients"][0]["status"] == "Success"){
             $response = "END To complete this transaction, please dial: ". $rest["meta"]["authorization"]["note"]. "\nYour bank code is: {$bankCode}.";
         }else{
             $response = "END Problem sending sms, Please dial: " . $rest["meta"]["authorization"]["note"] . "to complete this transaction. \nYour bank code is: {$bankCode}.";
         }
     }else{
         $response = "END Error initiating transaction, please try again!";
     }
}
elseif($ussd_string_exploded[0] == 6 && $level == 2 && $ussd_string_exploded[1] == "00"){
    $response  = "CON DSTV plan: \n";
    $response .= "1. Yanga N5,065 \n";
    $response .= "2. Padi N4,350 \n";
    $response .= "3. Compact N10,400 \n";
    $response .= "4. Premium N20,900 \n";
    $response .= "5. Compact Plus N14,900 \n";
    $response .= "6. DStv HDPVR Access Service N2,500";

}
elseif($ussd_string_exploded[0] == 6 && $level == 3 && $ussd_string_exploded[1] == "00"){
    $response = "CON Enter DSTV IUC No.\n";
    $response .= "0 Main Menu"; 
}
elseif($ussd_string_exploded[0] == 6 && $level == 4 && $ussd_string_exploded[1] == "00"){

    $verifyDSTV = $biller_list->VerifyCable('dstv', $ussd_string_exploded[3]);
    $verifydata = json_decode($verifyDSTV, true);

    switch($ussd_string_exploded[1]){
        case "1":
            $val = "DStv Yanga + ExtraView N5,065";
            break;
        case "2":
            $val = "DStv Padi + ExtraView N4,350";
            break;
        case "3":
            $val = "DStv Compact + Extra View N10,400";
            break;
        case "4":
            $val = "DStv Premium - Extra View N20,900";
            break;
        case "5":
            $val = "DStv Compact Plus - Extra View N14,900";
            break;
        default:
            $val = "DStv HDPVR Access Service N2,500";
    }

    $response = "CON Confirm: {$val} \n";
    $response .= "Name: {$verifydata["content"]["Customer_Name"]}\n";
    $response .= "Select Payment Type \n";
    $response .= "1. Bank USSD \n";
    $response .= "2. Wallet \n";
    $response .= "3. Debit Card \n";
    $response .= "4. New Debit Card \n";
    $response .= "0. Main Menu \n";
}
elseif( $ussd_string_exploded[0] == 6 && $level == 5 && $ussd_string_exploded[1] == "00"){
    $response = "CON Select your bank \n";
    $response .= "1. First Bank of Nigeria \n";
    $response .= "2. Zenith Bank \n";
    $response .= "3. Standard Chartered Bank \n";
    $response .= "4. Fidelity Bank \n";
    $response .= "5. Unity Bank \n";
    $response .= "6. JAIZ Bank \n";
    $response .= "7. Ecobank Plc \n";
    $response .= "00. Next";
}
elseif( $ussd_string_exploded[0] == 6 && $level == 6 && $ussd_string_exploded[1] == "00" && $ussd_string_exploded[5] != "00" ){
    // $response = "END You selected bank in level 5 \n";
    $phoneNumber = preg_replace('/^0/','+234', $rootNumber);
    switch($ussd_string_exploded[2]){
        case "1":
            $amtPaying = "5065";
            $variation_code = "yange-extra";
            break;
        case "2":
            $amtPaying = "4350";
            $variation_code = "padi-extra";
            break;
        case "3":
            $amtPaying = "10400";
            $variation_code = "dstv30";
            break;
        case "4":
            $amtPaying = "20900";
            $variation_code = "dstv33";
            break;
        default:
            $amtPaying = "14900";
            $variation_code = "dstv45";
    }
    switch($ussd_string_exploded[5]){
        case "1": //firstbank
            $bankCode = "137";
            break;
        case "2": //Zenith Bank
            $bankCode = "141";
            break;
        case "3": //Standard Chartered Bank
            $bankCode = "142";
            break;
        case "4": //Fidelity Bank
            $bankCode = "144";
            break;
        case "5": //Unity Bank
            $bankCode = "146";
            break;
        case "6": //JAIZ Bank
            $bankCode = "151";
            break;
        default: //Ecobank Plc
            $bankCode = "152";
    }
    $amtPaying = "50";
    $billersCode = $ussd_string_exploded[3];
    $whatToDo = "dstv~".$variation_code .'~'. $amtPaying . '~' . $billersCode . '~' . $phoneNumber .'~'. rand(000000, 999999);
    // $response = "END {$whatToDo}";
    $result = $init->MakePayment($amtPaying, $bankCode, $phoneNumber, $whatToDo);
    $rest = json_decode($result, TRUE);
    if($rest["status"] == "success"){
        $content = $rest["meta"]["authorization"]["note"]. "\nYour bank code is: {$bankCode}.";
        $ATsms = new AfricasTalkingCustom();
        $result = $ATsms->SendSms($phoneNumber, $content);
        $res = json_encode($result["data"]);
        $final_res = json_decode($res, true);
        if($final_res["SMSMessageData"]["Recipients"][0]["status"] == "Success"){
            $response = "END To complete this transaction, please dial: ". $rest["meta"]["authorization"]["note"]. "\nYour bank code is: {$bankCode}.";
        }else{
            $response = "END Problem sending sms, Please dial: " . $rest["meta"]["authorization"]["note"] . "to complete this transaction. \nYour bank code is: {$bankCode}.";
        }
    }else{
        $response = "END Error initiating transaction, please try again!";
    }
}
elseif( $ussd_string_exploded[0] == 6 && $level == 6 && $ussd_string_exploded[1] == "00" && $ussd_string_exploded[5] == "00" ){
    $response = "CON Select your bank \n";
    $response .= "1. Stanbic IBTC Bank \n";
    $response .= "2. Diamond Bank \n";
    $response .= "3. Heritage \n";
    $response .= "4. GTBank Plc \n";
    $response .= "5. Union Bank \n";
    $response .= "6. Sterling Bank \n";
    $response .= "7. Skye Bank \n";
    $response .= "00. Next";
}
elseif( $ussd_string_exploded[0] == 6 && $level == 7 && $ussd_string_exploded[1] == "00" && $ussd_string_exploded[6] != "00" ){
    // $response = "END You selected bank in level 5 \n";
    $phoneNumber = preg_replace('/^0/','+234', $rootNumber);
    switch($ussd_string_exploded[2]){
        case "1":
            $amtPaying = "5065";
            $variation_code = "yange-extra";
            break;
        case "2":
            $amtPaying = "4350";
            $variation_code = "padi-extra";
            break;
        case "3":
            $amtPaying = "10400";
            $variation_code = "dstv30";
            break;
        case "4":
            $amtPaying = "20900";
            $variation_code = "dstv33";
            break;
        default:
            $amtPaying = "14900";
            $variation_code = "dstv45";
    }
    switch($ussd_string_exploded[6]){
        case "1": //Stanbic IBTC Bank
            $bankCode = "158";
            break;
        case "2": //Diamond Bank
            $bankCode = "170";
            break;
        case "3": //Heritage
            $bankCode = "175";
            break;
        case "4": //GTBank Plc
            $bankCode = "177";
            break;
        case "5": //Union Bank
            $bankCode = "178";
            break;
        case "6": //Sterling Bank
            $bankCode = "179";
            break;
        default: //Skye Bank
            $bankCode = "180";
    }
    $amtPaying = "50";
    $billersCode = $ussd_string_exploded[3];
    $whatToDo = "dstv~".$variation_code .'~'. $amtPaying . '~' . $billersCode . '~' . $phoneNumber .'~'. rand(000000, 999999);
    // $response = "END {$whatToDo}";
    $result = $init->MakePayment($amtPaying, $bankCode, $phoneNumber, $whatToDo);
    $rest = json_decode($result, TRUE);
    if($rest["status"] == "success"){
        $content = $rest["meta"]["authorization"]["note"]. "\nYour bank code is: {$bankCode}.";
        $ATsms = new AfricasTalkingCustom();
        $result = $ATsms->SendSms($phoneNumber, $content);
        $res = json_encode($result["data"]);
        $final_res = json_decode($res, true);
        if($final_res["SMSMessageData"]["Recipients"][0]["status"] == "Success"){
            $response = "END To complete this transaction, please dial: ". $rest["meta"]["authorization"]["note"]. "\nYour bank code is: {$bankCode}.";
        }else{
            $response = "END Problem sending sms, Please dial: " . $rest["meta"]["authorization"]["note"] . "to complete this transaction.\nYour bank code is: {$bankCode}.";
        }
    }else{
        $response = "END Error initiating transaction, please try again!";
    }
}
elseif($ussd_string_exploded[0] == 6 && $level == 7 && $ussd_string_exploded[1] == "00" && $ussd_string_exploded[6] == "00"){
    $response = "CON Select your bank \n";
    $response .= "1. Keystone Bank \n";
    $response .= "2. Fidelity  \n";
    $response .= "3. First City Monument Bank \n";
    $response .= "4. United Bank for Africa \n";
    $response .= "5. Access Bank \n";
    $response .= "6. Wema Bank \n";
}
elseif($ussd_string_exploded[0] == 6 && $ussd_string_exploded[1] == "00" && $level == 8){
    switch($ussd_string_exploded[2]){
        case "1":
            $amtPaying = "5065";
            $variation_code = "yange-extra";
            break;
        case "2":
            $amtPaying = "4350";
            $variation_code = "padi-extra";
            break;
        case "3":
            $amtPaying = "10400";
            $variation_code = "dstv30";
            break;
        case "4":
            $amtPaying = "20900";
            $variation_code = "dstv33";
            break;
        default:
            $amtPaying = "14900";
            $variation_code = "dstv45";
    }
    switch($ussd_string_exploded[7]){
        case "1": //Keystone Bank
            $bankCode = "181";
            break;
        case "2": //Fidelity
            $bankCode = "144";
            break;
        case "3": //First City Monument Bank
            $bankCode = "186";
            break;
        case "4": //United Bank for Africa
            $bankCode = "190";
            break;
        case "5": //Access Bank
            $bankCode = "191";
            break;
        default: //Wema Bank
            $bankCode = "168";
    }
    $amtPaying = "50";
    $billersCode = $ussd_string_exploded[3];
    $phoneNumber = preg_replace('/^0/','+234', $rootNumber);
    $whatToDo = "dstv~".$variation_code .'~'. $amtPaying . '~' . $billersCode . '~' . $phoneNumber .'~'. rand(000000, 999999);
    // $response = "END {$whatToDo}";
    $result = $init->MakePayment($amtPaying, $bankCode, $phoneNumber, $whatToDo);
    $rest = json_decode($result, TRUE);
    if($rest["status"] == "success"){
        $content = $rest["meta"]["authorization"]["note"]. "\nYour bank code is: {$bankCode}.";
        $ATsms = new AfricasTalkingCustom();
        $result = $ATsms->SendSms($phoneNumber, $content);
        $res = json_encode($result["data"]);
        $final_res = json_decode($res, true);
        if($final_res["SMSMessageData"]["Recipients"][0]["status"] == "Success"){
            $response = "END To complete this transaction, please dial: ". $rest["meta"]["authorization"]["note"]. "\nYour bank code is: {$bankCode}.";
        }else{
            $response = "END Problem sending sms, Please dial: " . $rest["meta"]["authorization"]["note"] . "to complete this transaction.\nYour bank code is: {$bankCode}.";
        }
    }else{
        $response = "END Error initiating transaction, please try again!";
    }
}
elseif( $ussd_string_exploded[0] == 6 && $level == 4 && $ussd_string_exploded[3] == "2" ){
    $response = "CON Your wallet balance: \n";
    $response .= "1. Proceed \n";
    $response .= "2. Cancel \n";
    $response .= "0. Main Menu \n";
}
//Final payment through walleting
elseif($ussd_string_exploded[0] == 6 && $level == 5 && $ussd_string_exploded[3] == "2"){
    $response = "END You are about to purchase of DSTV tariff. Please wait while you are being redirected to make payment \n";
}
////////////////////////////////////////////////
////////////// End of DSTV ////////////////////
//////////////////////////////////////////////





////////////////////////////////////////////////
////////////// Start of WAEC //////////////////
//////////////////////////////////////////////
elseif ($text == "7") {


    $getUserWallet = $wallet->getUser($rootNumber);
    $wallet = json_decode($getUserWallet, true);
    $wallet_acct_bal = $wallet['account_balance'];
    $wallet_pin = $wallet['pin'];
    $wallet_status = $wallet['status'];


    $response = "END Coming soon!!!";
    // $response  = "CON Select Type: \n";
    // $response .= "1. WAEC Result Checker \n";
    // $response .= "2. WAEC Registration PIN \n";
    // $response .= "0. Main Menu";
}
elseif($ussd_string_exploded[0] == 7 && $level == "2"){
    $response = "CON Select Exam Type: \n";
    $response .= "1. MAY/JUNE – N1800 \n";
    $response .= "2. WASSCE/GCE – N10,000 \n";
    $response .= "0. Main Menu";
}
elseif($ussd_string_exploded[0] == 7 && $level == 3){
    if($ussd_string_exploded[1] == "1"):
        $pack = "MAY/JUNE – N8,500";
    elseif($ussd_string_exploded[1] == "2"):
        $pack = "WASSCE/GCE – N10,000";
    endif;
    $response = "CON Confirm :".$pack." \n";
    $response .= "<em>Select Payment Type</em> \n";
    $response .= "1. Bank USSD \n";
    $response .= "2. Wallet \n";
    $response .= "3. Debit Card \n";
    $response .= "4. New Debit Card \n";
    $response .= "0. Main Menu \n";
}
elseif( $ussd_string_exploded[0] == 7 && $level == 4 && $ussd_string_exploded[3] == "1" ){
    $response = "CON Select your bank \n";
    $response .= "1. Access \n";
    $response .= "2. FCMB \n";
    $response .= "3. GTB \n";
    $response .= "4. Sterling \n";
    $response .= "5. UBA \n";
    $response .= "6. Zenith \n";
    $response .= "7. Next \n";
    $response .= "0. Main Menu \n";
}
elseif( $ussd_string_exploded[0] == 7 && $level == 5 && $ussd_string_exploded[3] == "1" ){
    $response = "CON Your account number: \n";
    $response .= "0. Main Menu \n";
}
//final payment using bank ussd
elseif($ussd_string_exploded[0] == 7 && $level == 6 && $ussd_string_exploded[3] == "1"){
    if($ussd_string_exploded[1] == "1"):
        $pack = "MAY/JUNE – N8,500";
    elseif($ussd_string_exploded[1] == "2"):
        $pack = "WASSCE/GCE – N10,000";
    endif;
    $response = "END You are about to purchase of {$pack}, Please wait while you are being redirected to make payment \n";
}
elseif( $ussd_string_exploded[0] == 7 && $level == 4 && $ussd_string_exploded[3] == "2" ){
    $response = "CON Your wallet balance: \n";
    $response .= "1. Proceed \n";
    $response .= "2. Cancel \n";
    $response .= "0. Main Menu \n";
}
//Final payment through walleting
elseif($ussd_string_exploded[0] == 7 && $level == 5 && $ussd_string_exploded[3] == "2"){
    if($ussd_string_exploded[1] == "1"):
        $pack = "MAY/JUNE – N8,500";
    elseif($ussd_string_exploded[1] == "2"):
        $pack = "WASSCE/GCE – N10,000";
    endif;
    $response = "END You are about to purchase of {$pack}. Please wait while you are being redirected to make payment \n";
}
////////////////////////////////////////////////
////////////// End of WAEC ////////////////////
//////////////////////////////////////////////





////////////////////////////////////////////////
////////////// Start of JAMB //////////////////
//////////////////////////////////////////////
// elseif ($text == "8") {

    
//     $getUserWallet = $wallet->getUser($rootNumber);
//     $wallet = json_decode($getUserWallet, true);
//     $wallet_acct_bal = $wallet['account_balance'];
//     $wallet_pin = $wallet['pin'];
//     $wallet_status = $wallet['status'];


//     $response = "END Coming soon!!!";
//     // $response  = "CON Select Type: \n";
//     // $response .= "1. UTME Result Checker \n";
//     // $response .= "2. UTME Registration PIN \n";
//     // $response .= "0. Main Menu";
// }
// elseif($ussd_string_exploded[0] == 8 && $level == 2){
//     if($ussd_string_exploded[1] == "1"):
//         $pack = "UTME Result Checker – N8,500";
//     elseif($ussd_string_exploded[1] == "2"):
//         $pack = "UTME Registration – N10,000";
//     endif;
//     $response = "CON Confirm : ".$pack." \n";
//     $response .= "<em>Select Payment Type</em> \n";
//     $response .= "1. Bank USSD \n";
//     $response .= "2. Wallet \n";
//     $response .= "3. Debit Card \n";
//     $response .= "4. New Debit Card \n";
//     $response .= "0. Main Menu \n";
// }
// elseif( $ussd_string_exploded[0] == 8 && $level == 3 && $ussd_string_exploded[2] == "1" ){
//     $response = "CON Select your bank \n";
//     $response .= "1. Access \n";
//     $response .= "2. FCMB \n";
//     $response .= "3. GTB \n";
//     $response .= "4. Sterling \n";
//     $response .= "5. UBA \n";
//     $response .= "6. Zenith \n";
//     $response .= "7. Next \n";
//     $response .= "0. Main Menu \n";
// }
// elseif( $ussd_string_exploded[0] == 8 && $level == 4 && $ussd_string_exploded[2] == "1" ){
//     $response = "CON Your account number: \n";
//     $response .= "0. Main Menu \n";
// }
// //final payment using bank ussd
// elseif($ussd_string_exploded[0] == 8 && $level == 5 && $ussd_string_exploded[2] == "1"){
//     if($ussd_string_exploded[1] == "1"):
//         $pack = "UTME Result Checker – N8,500";
//     elseif($ussd_string_exploded[1] == "2"):
//         $pack = "UTME Registration – N10,000";
//     endif;
//     $response = "END You are about to purchase of {$pack}. Please wait while you are being redirected to make payment \n";
// }
// elseif( $ussd_string_exploded[0] == 8 && $level == 3 && $ussd_string_exploded[2] == "2" ){
//     $response = "CON Your wallet balance: \n";
//     $response .= "1. Proceed \n";
//     $response .= "2. Cancel \n";
//     $response .= "0. Main Menu \n";
// }
// //Final payment through walleting
// elseif($ussd_string_exploded[0] == 8 && $level == 4 && $ussd_string_exploded[2] == "2"){
//     if($ussd_string_exploded[1] == "1"):
//         $pack = "UTME Result Checker – N8,500";
//     elseif($ussd_string_exploded[1] == "2"):
//         $pack = "UTME Registration – N10,000";
//     endif;
//     $response = "END You are about to purchase of {$pack}. Please wait while you are being redirected to make payment \n";
// }
////////////////////////////////////////////////
////////////// End of JAMB ////////////////////
//////////////////////////////////////////////





///////////////////////////////////////////////////////////
////////////// Start of School registration //////////////
/////////////////////////////////////////////////////////
elseif ($text == "8") {

    
    $getUserWallet = $wallet->getUser($rootNumber);
    $wallet = json_decode($getUserWallet, true);
    $wallet_acct_bal = $wallet['account_balance'];
    $wallet_pin = $wallet['pin'];
    $wallet_status = $wallet['status'];

    
//    $response = "END Coming soon!!!";
     $response  = "CON Select School/Partner \n";
    foreach($all_partner['body'] as $item){
        $response .= $item['id'].' '.$item['org_name']."\n";
    }
     $response .= "0. Main Menu \n";
}
elseif($ussd_string_exploded[0] == 8 && $level == 2){
    $response = "CON Enter Payment ID/Payment Code to continue: \n";
    $response .= "0. Main Menu \n";
}
elseif($ussd_string_exploded[0] == 8 && $level == 3){
    $org_id = $ussd_string_exploded[1];
    $org_code = $ussd_string_exploded[2];

    
    $payment_details = $activity->getPartnerPayment($org_id, $org_code);


    $response = "CON Payment for : ".$org_name." \n";
    $response .= "Select Payment Type \n";
    $response .= "1. Bank USSD \n";
    $response .= "2. Wallet \n";
    $response .= "3. Debit Card \n";
    $response .= "4. New Debit Card \n";
    $response .= "0. Main Menu \n";
}
elseif( $ussd_string_exploded[0] == 8 && $level == 4 && $ussd_string_exploded[3] == "1" ){
    $response = "CON Select your bank \n";
    $response .= "1. Access \n";
    $response .= "2. FCMB \n";
    $response .= "3. GTB \n";
    $response .= "4. Sterling \n";
    $response .= "5. UBA \n";
    $response .= "6. Zenith \n";
    $response .= "7. Next \n";
    $response .= "0. Main Menu \n";
}
elseif( $ussd_string_exploded[0] == 8 && $level == 5 && $ussd_string_exploded[3] == "1" ){
    $response = "CON Your account number: \n";
    $response .= "0. Main Menu \n";
}
//final payment using bank ussd
elseif($ussd_string_exploded[0] == 8 && $level == 6 && $ussd_string_exploded[3] == "1"){
    if($ussd_string_exploded[1] == "1"):
        $pack = "Osogbo Intl Sch";
    elseif($ussd_string_exploded[1] == "2"):
        $pack = "CAC-Oke Ola";
    elseif($ussd_string_exploded[1] == "3"):
        $pack = "Osun Subeb Form";
    elseif($ussd_string_exploded[1] == "4"):
        $pack = "LayLand Schools";
    elseif($ussd_string_exploded[1] == "5"):
        $pack = "Ede Baptist Church";
    elseif($ussd_string_exploded[1] == "6"):
        $pack = "Ede North Farm Credit";
    endif;
    $response = "END You are about to purchase of {$pack}. Please wait while you are being redirected to make payment \n";
}
elseif( $ussd_string_exploded[0] == 8 && $level == 4 && $ussd_string_exploded[3] == "2" ){
    $response = "CON Your wallet balance: \n";
    $response .= "1. Proceed \n";
    $response .= "2. Cancel \n";
    $response .= "0. Main Menu \n";
}
//Final payment through walleting
elseif($ussd_string_exploded[0] == 8 && $level == 5 && $ussd_string_exploded[3] == "2"){
    if($ussd_string_exploded[1] == "1"):
        $pack = "Osogbo Intl Sch";
    elseif($ussd_string_exploded[1] == "2"):
        $pack = "CAC-Oke Ola";
    elseif($ussd_string_exploded[1] == "3"):
        $pack = "Osun Subeb Form";
    elseif($ussd_string_exploded[1] == "4"):
        $pack = "LayLand Schools";
    elseif($ussd_string_exploded[1] == "5"):
        $pack = "Ede Baptist Church";
    elseif($ussd_string_exploded[1] == "6"):
        $pack = "Ede North Farm Credit";
    endif;
    $response = "END You are about to purchase of {$pack}. Please wait while you are being redirected to make payment \n";
}
///////////////////////////////////////////////////////////
////////////// End of School registration ////////////////
/////////////////////////////////////////////////////////


// send your response back to the API
header("Content-type: text/plain; charset=utf-8");
echo $response;

?>