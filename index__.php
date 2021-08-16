<?php
require_once "api_/constants.php";
require_once "api_/categories_handler.php";
require_once "sample-test-script.php";

$sessionId   = $_POST['sessionId'];
$serviceCode = $_POST['serviceCode'];
$text        = $_POST['text'];
$phonenumber = $_POST['MSISDN'];

// use explode to split the string text response from Africa's talking gateway into an array.
$ussd_string_exploded = explode("*", $text);
// Get ussd menu level number from the gateway
$level = count($ussd_string_exploded);
if ($text == "") {
    $response="CON Welcome! Select an Option \n";
    $response .= "1. Buy Airtime \n";
    $response .= "2. Buy Data \n";
    $response .= "3. Wallet \n";
    $response .= "4. Electricity Bill \n";
    $response .= "5. GoTV \n";
    $response .= "6. DSTV \n";
    $response .= "7. WAEC Card \n";
    $response .= "8. Jamb Card \n";
    $response .= "9. Schools & Orgs \n";
}

/////////////////////////////////////////////////////////
//////////// Start of airtime purchase /////////////////
///////////////////////////////////////////////////////
elseif ($text == "1") {
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
    $response = "CON Enter Phone number: \n";
    $response .= "0. Main Menu \n";
}
elseif($ussd_string_exploded[0] == 1 && $level == 4){
    $response = "CON Confirm airtime: N".$ussd_string_exploded[2]." \n";
    $response .= "For: ".$ussd_string_exploded[3]." \n";
    $response .= "<em>Select Payment Type:</em> \n";
    $response .= "1. Bank USSD \n";
    $response .= "2. Wallet \n";
    $response .= "3. Debit Card \n";
    $response .= "4. New Debit Card \n";
    $response .= "0. Main Menu \n";
}
// elseif( $ussd_string_exploded[0] == 1 && $level == 5 && $ussd_string_exploded[4] == "1" ){
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
// elseif( $ussd_string_exploded[0] == 1 && $level == 6 && $ussd_string_exploded[4] == "1" ){
//     $response = "CON Your account number: \n";
//     $response .= "0. Main Menu \n";
// }
//final payment using bank ussd
elseif($ussd_string_exploded[0] == 1 && $level == 5 && $ussd_string_exploded[4] == "1"){
    if($ussd_string_exploded[1] == 1){
        $network = "MTN";
    }elseif($ussd_string_exploded[1] == 2){
        $network = "GLO";
    }elseif($ussd_string_exploded[1] == 3){
        $network = "Airtel";
    }elseif($ussd_string_exploded[1] == 4){
        $network = "9Mobile";
    }
    $data = array(
        'tx_ref'=>rand(00000000, 99999999),
        'account_bank' =>$ussd_string_exploded[6],
        'currency' => 'NG',
        'amount' => $ussd_string_exploded[2],
        'email'=>'ussdpay@wolid.com',
        'phone_number'=> $ussd_string_exploded[3],
        'fullname' => 'ussd pay'
    );
    $response = "END This is the end";

    // echo json_decode($response, true);
    // echo json_encode($decoded['ResponseHeader']['ResponseMessage']);
    // $response = "END You are about to purchase N".$ussd_string_exploded[2]." ".$network." airtime, recipient: ".$ussd_string_exploded[3].". Please wait while you are being redirected to make payment \n";
}
elseif( $ussd_string_exploded[0] == 1 && $level == 5 && $ussd_string_exploded[4] == "2" ){
    $response = "CON Your wallet balance: \n";
    $response .= "1. Proceed \n";
    $response .= "2. Cancel \n";
    $response .= "0. Main Menu \n";
}
//Final payment through walleting
elseif($ussd_string_exploded[0] == 1 && $level == 6 && $ussd_string_exploded[4] == "2"){

    if($ussd_string_exploded[1] == 1){
        $network = "MTN";
    }elseif($ussd_string_exploded[1] == 2){
        $network = "GLO";
    }elseif($ussd_string_exploded[1] == 3){
        $network = "Airtel";
    }
    elseif($ussd_string_exploded[1] == 4){
        $network = "9Mobile";
    }
    $response = "END You are about to purchase N".$ussd_string_exploded[2]." ".$network." airtime, recipient: ".$ussd_string_exploded[3].". Please wait while you are being redirected to make payment \n";
}
///////////////////////////////////////////////////////////
////////////// End of airtime purchase ///////////////////
/////////////////////////////////////////////////////////





///////////////////////////////////////////////////////
////////////// Start of Data purchase ////////////////
/////////////////////////////////////////////////////
elseif ($text == "2") {
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
    $response .= "1. 1000MB \n";
    $response .= "2. 2000MB \n";
    $response .= "3. 3000MB \n";
    $response .= "4. 4000MB \n";
    $response .= "5. 5000MB \n";
    $response .= "6. 6000MB \n";
    $response .= "7. 7000MB \n";
    $response .= "0. Main Menu \n";
}
elseif($ussd_string_exploded[0] == 2 && $level == 3){
    $response = "CON Enter recipient phone number: \n";
    $response .= "0. Main Menu \n";
}
elseif($ussd_string_exploded[0] == 2 && $level == 4){
    $response = "CON Confirm data plan: ".$ussd_string_exploded[2]."GB \n";
    $response .= "To: ".$ussd_string_exploded[3]."\n";
    $response .= "<em>Select Payment Type</em> \n";
    $response .= "1. Bank USSD \n";
    $response .= "2. Wallet \n";
    $response .= "3. Debit Card \n";
    $response .= "4. New Debit Card \n";
    $response .= "0. Main Menu \n";
}
elseif( $ussd_string_exploded[0] == 2 && $level == 5 && $ussd_string_exploded[4] == "1" ){
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
elseif( $ussd_string_exploded[0] == 2 && $level == 6 && $ussd_string_exploded[4] == "1" ){
    $response = "CON Your account number: \n";
    $response .= "0. Main Menu \n";
}
//final payment using bank ussd
elseif($ussd_string_exploded[0] == 2 && $level == 7 && $ussd_string_exploded[4] == "1"){
    $data = array(
        'tx_ref'=>rand(00000000, 99999999),
        'account_bank' =>$ussd_string_exploded[6],
        'currency' => 'NG',
        'amount' => $ussd_string_exploded[2],
        'email'=>'ussdpay@wolid.com',
        'phone_number'=> $ussd_string_exploded[3],
        'fullname' => 'ussd pay'
    );

    if($ussd_string_exploded[1] == 1){
        $network = "MTN";
    }elseif($ussd_string_exploded[1] == 2){
        $network = "GLO";
    }elseif($ussd_string_exploded[1] == 3){
        $network = "Airtel";
    }
    elseif($ussd_string_exploded[1] == 4){
        $network = "9Mobile";
    }
    $response = "END You are about to purchase ".$ussd_string_exploded[2]."GB ".$network." data plan, recipient: ".$ussd_string_exploded[3].". Please wait while you are being redirected to make payment \n";
}
elseif( $ussd_string_exploded[0] == 2 && $level == 5 && $ussd_string_exploded[4] == "2" ){
    $response = "CON Your wallet balance: \n";
    $response .= "1. Proceed \n";
    $response .= "2. Cancel \n";
    $response .= "0. Main Menu \n";
}
//Final payment through walleting
elseif($ussd_string_exploded[0] == 2 && $level == 6 && $ussd_string_exploded[4] == "2"){
    if($ussd_string_exploded[1] == 1){
        $network = "MTN";
    }elseif($ussd_string_exploded[1] == 2){
        $network = "GLO";
    }elseif($ussd_string_exploded[1] == 3){
        $network = "Airtel";
    }
    elseif($ussd_string_exploded[1] == 4){
        $network = "9Mobile";
    }
    $response = "END You are about to purchase ".$ussd_string_exploded[2]."GB ".$network." data plan, recipient: ".$ussd_string_exploded[3].". Please wait while you are being redirected to make payment \n";
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
    $response .= "0. Main Menu";
}
//to check wallet balance
elseif ($ussd_string_exploded[0] == 3 && $ussd_string_exploded[1] == 1 && $level == 2) {
    $response = "CON Input your 4 digit pin: \n";
    $response .= "0. Main Menu \n";
}
elseif($ussd_string_exploded[0] == 3 && $ussd_string_exploded[1] == 1 && $level == 3){
    $response = "END Your balance is: \n";
    $response .= "0. Main Menu \n";
}
//to fund wallet
elseif($ussd_string_exploded[0] == 3 && $ussd_string_exploded[1] == 2 && $level == 2){
    $response = "CON Amount to fund: \n";
    $response .= "0. Main Menu \n";
}
elseif($ussd_string_exploded[0] == 3 && $ussd_string_exploded[1] == 2 && $level == 3 ){
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
elseif( $ussd_string_exploded[0] == 3 && $ussd_string_exploded[1] == 2  && $level == 4 ){
    $response = "CON Your account number: \n";
    $response .= "0. Main Menu \n";
}
//final payment using bank ussd
elseif($ussd_string_exploded[0] == 3 && $ussd_string_exploded[1] == 2  && $level == 5 ){
    $response = "END Please wait while you are being redirected to make payment \n";
}
//////////////////////////////////////////////////
////////////// End of Walleting /////////////////
////////////////////////////////////////////////





////////////////////////////////////////////////
////////////// Start of electricity bill //////
//////////////////////////////////////////////
else if($text == "4") {
    $response  = "CON Buy Electricity Token: \n";
    $response .= "1. AEDC \n";
    $response .= "2. EEDC \n";
    $response .= "3. EKEDC \n";
    $response .= "4. IBEDC \n";
    $response .= "5. IE \n";
    $response .= "6. JED \n";
    $response .= "7. KED \n";
    $response .= "8. KEDCO  \n";
    $response .= "9. PHED \n";
    $response .= "0. Main Menu";
}
elseif($ussd_string_exploded[0] == 4 && $level == 2){
    if($ussd_string_exploded[1] == 1):
        $pack = "AEDC";
    elseif($ussd_string_exploded[1] == 2):
        $pack = "EEDC";
    elseif($ussd_string_exploded[1] == 3):
        $pack = "EKEDC";
    elseif($ussd_string_exploded[1] == 4):
        $pack = "IBEDC";
    elseif($ussd_string_exploded[1] == 5):
        $pack = "IE";
    elseif($ussd_string_exploded[1] == 6):
        $pack = "JED";
    elseif($ussd_string_exploded[1] == 7):
        $pack = "KED";
    elseif($ussd_string_exploded[1] == 8):
        $pack = "KEDCO";
    elseif($ussd_string_exploded[1] == 9):
        $pack = "PHED";
    endif;

    $response = "CON Select package: \n";
    $response .= "1. {$pack} – Postpaid \n";
    $response .= "2. {$pack} - Prepaid \n";
    $response .= "0. Main Menu" ;
}
elseif($ussd_string_exploded[0] == 4 && $level == 3){
    $response = "CON Enter meter no.: \n";
    $response .= "0. Main Menu" ;
}
elseif($ussd_string_exploded[0] == 4 && $level == 4){
    $response = "CON Enter amount: \n";
    $response .= "0. Main Menu" ;
}
elseif($ussd_string_exploded[0] == 4 && $level == 5){
    if($ussd_string_exploded[1] == 1):
        if($ussd_string_exploded[2] == 1):
            $pack = "AEDC Prepaid";
        else:
            $pack = "AEDC Postpaid";
        endif;
    elseif($ussd_string_exploded[1] == 2):
        if($ussd_string_exploded[2] == 1):
            $pack = "EEDC Prepaid";
        else:
            $pack = "EEDC Postpaid";
        endif;
    elseif($ussd_string_exploded[1] == 3):
        if($ussd_string_exploded[2] == 1):
            $pack = "EKEDC Prepaid";
        else:
            $pack = "EKEDC Postpaid";
        endif;
    elseif($ussd_string_exploded[1] == 4):
        if($ussd_string_exploded[2] == 1):
            $pack = "IBEDC Prepaid";
        else:
            $pack = "IBEDC Postpaid";
        endif;
    elseif($ussd_string_exploded[1] == 5):
        if($ussd_string_exploded[2] == 1):
            $pack = "IE Prepaid";
        else:
            $pack = "IE Postpaid";
        endif;
    elseif($ussd_string_exploded[1] == 6):
        if($ussd_string_exploded[2] == 1):
            $pack = "JED Prepaid";
        else:
            $pack = "JED Postpaid";
        endif;
    elseif($ussd_string_exploded[1] == 7):
        if($ussd_string_exploded[2] == 1):
            $pack = "KED Prepaid";
        else:
            $pack = "KED Postpaid";
        endif;
    elseif($ussd_string_exploded[1] == 8):
        if($ussd_string_exploded[2] == 1):
            $pack = "KEDCO Prepaid";
        else:
            $pack = "KEDCO Postpaid";
        endif;
    elseif($ussd_string_exploded[1] == 9):
        if($ussd_string_exploded[2] == 1):
            $pack = "PHED Prepaid";
        else:
            $pack = "PHED Postpaid";
        endif;
    endif;
    
    $response = "CON Confirm : ".$pack." \n";
    $response .= "Name: Checking...\n";
    $response .= "<em>Select Payment Type</em> \n";
    $response .= "1. Bank USSD \n";
    $response .= "2. Wallet \n";
    $response .= "3. Debit Card \n";
    $response .= "4. New Debit Card \n";
    $response .= "0. Main Menu \n";
}
elseif( $ussd_string_exploded[0] == 4 && $level == 6 && $ussd_string_exploded[5] == "1" ){
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
elseif( $ussd_string_exploded[0] == 4 && $level == 7 && $ussd_string_exploded[5] == "1" ){
    $response = "CON Your account number: \n";
    $response .= "0. Main Menu \n";
}
//final payment using bank ussd
elseif($ussd_string_exploded[0] == 4 && $level == 8 && $ussd_string_exploded[5] == "1"){
    // $data = array(
    //     'tx_ref'=>rand(00000000, 99999999),
    //     'account_bank' =>$ussd_string_exploded[6],
    //     'currency' => 'NG',
    //     'amount' => $ussd_string_exploded[2],
    //     'email'=>'ussdpay@wolid.com',
    //     'phone_number'=> $ussd_string_exploded[3],
    //     'fullname' => 'ussd pay'
    // );
    $response = "END You are about to purchase of electricity tariff. Please wait while you are being redirected to make payment \n";
}
elseif( $ussd_string_exploded[0] == 4 && $level == 7 && $ussd_string_exploded[5] == "2" ){
    $response = "CON Your wallet balance: \n";
    $response .= "1. Proceed \n";
    $response .= "2. Cancel \n";
    $response .= "0. Main Menu \n";
}
//Final payment through walleting
elseif($ussd_string_exploded[0] == 4 && $level == 8 && $ussd_string_exploded[5] == "2"){
    $response = "END You are about to purchase of electricity tariff. Please wait while you are being redirected to make payment \n";
}
///////////////////////////////////////////////////////////
////////////// End of electricity bill ////////////////////
//////////////////////////////////////////////////////////





////////////////////////////////////////////////
////////////// Start of GoTV //////////////////
//////////////////////////////////////////////
elseif($text == "5") {
    $response  = "CON GoTV Recharge: \n";
    $response .= "1. Smallie \n";
    $response .= "2. Jinja \n";
    $response .= "3. Jolli \n";
    $response .= "4. Max \n";
    $response .= "0. Main Menu \n";
}
elseif($ussd_string_exploded[0] == 5 && $level == 2){
    $response = "CON Enter GOTV IUC No. \n";
    $response .= "0 Main Menu"; 
}
elseif($ussd_string_exploded[0] == 5 && $level == 3){
    if($ussd_string_exploded[1] == "1"):
        $pack = "Smallie";
    elseif($ussd_string_exploded[1] == "2"):
        $pack = "Jinja";
    elseif($ussd_string_exploded[1] == "3"):
        $pack = "Jolli";
    elseif($ussd_string_exploded[1] == "4"):
        $pack = "Max";
    endif;
    $response = "CON Confirm : GoTV - ".$pack." \n";
    $response .= "Name: Checking...\n";
    $response .= "<em>Select Payment Type</em> \n";
    $response .= "1. Bank USSD \n";
    $response .= "2. Wallet \n";
    $response .= "3. Debit Card \n";
    $response .= "4. New Debit Card \n";
    $response .= "0. Main Menu \n";
}
elseif( $ussd_string_exploded[0] == 5 && $level == 4 && $ussd_string_exploded[3] == "1" ){
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
elseif( $ussd_string_exploded[0] == 5 && $level == 5 && $ussd_string_exploded[3] == "1" ){
    $response = "CON Your account number: \n";
    $response .= "0. Main Menu \n";
}
//final payment using bank ussd
elseif($ussd_string_exploded[0] == 5 && $level == 6 && $ussd_string_exploded[3] == "1"){
    $response = "END You are about to purchase of GoTV tariff. Please wait while you are being redirected to make payment \n";
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
    $response  = "CON GoTV Recharge: \n";
    $response .= "1. Smallie \n";
    $response .= "2. Jinja \n";
    $response .= "3. Jolli \n";
    $response .= "4. Max \n";
    $response .= "0. Main Menu \n";
}
elseif($ussd_string_exploded[0] == 6 && $level == 2){
    $response = "CON Enter DSTV IUC No. \n";
    $response .= "0 Main Menu"; 
}
elseif($ussd_string_exploded[0] == 6 && $level == 3){
    if($ussd_string_exploded[1] == "1"):
        $pack = "Smallie";
    elseif($ussd_string_exploded[1] == "2"):
        $pack = "Jinja";
    elseif($ussd_string_exploded[1] == "3"):
        $pack = "Jolli";
    elseif($ussd_string_exploded[1] == "4"):
        $pack = "Max";
    endif;
    $response = "CON Confirm : DSTV - ".$pack." \n";
    $response .= "Name: Checking...\n";
    $response .= "<em>Select Payment Type</em> \n";
    $response .= "1. Bank USSD \n";
    $response .= "2. Wallet \n";
    $response .= "3. Debit Card \n";
    $response .= "4. New Debit Card \n";
    $response .= "0. Main Menu \n";
}
elseif( $ussd_string_exploded[0] == 6 && $level == 4 && $ussd_string_exploded[3] == "1" ){
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
elseif( $ussd_string_exploded[0] == 6 && $level == 5 && $ussd_string_exploded[3] == "1" ){
    $response = "CON Your account number: \n";
    $response .= "0. Main Menu \n";
}
//final payment using bank ussd
elseif($ussd_string_exploded[0] == 6 && $level == 6 && $ussd_string_exploded[3] == "1"){
    $response = "END You are about to purchase of DSTV tariff. Please wait while you are being redirected to make payment \n";
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
    $response  = "CON Select Type: \n";
    $response .= "1. WAEC Result Checker \n";
    $response .= "2. WAEC Registration PIN \n";
    $response .= "0. Main Menu";
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
elseif ($text == "8") {
    $response  = "CON Select Type: \n";
    $response .= "1. UTME Result Checker \n";
    $response .= "2. UTME Registration PIN \n";
    $response .= "0. Main Menu";
}
elseif($ussd_string_exploded[0] == 8 && $level == 2){
    if($ussd_string_exploded[1] == "1"):
        $pack = "UTME Result Checker – N8,500";
    elseif($ussd_string_exploded[1] == "2"):
        $pack = "UTME Registration – N10,000";
    endif;
    $response = "CON Confirm : ".$pack." \n";
    $response .= "<em>Select Payment Type</em> \n";
    $response .= "1. Bank USSD \n";
    $response .= "2. Wallet \n";
    $response .= "3. Debit Card \n";
    $response .= "4. New Debit Card \n";
    $response .= "0. Main Menu \n";
}
elseif( $ussd_string_exploded[0] == 8 && $level == 3 && $ussd_string_exploded[2] == "1" ){
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
elseif( $ussd_string_exploded[0] == 8 && $level == 4 && $ussd_string_exploded[2] == "1" ){
    $response = "CON Your account number: \n";
    $response .= "0. Main Menu \n";
}
//final payment using bank ussd
elseif($ussd_string_exploded[0] == 8 && $level == 5 && $ussd_string_exploded[2] == "1"){
    if($ussd_string_exploded[1] == "1"):
        $pack = "UTME Result Checker – N8,500";
    elseif($ussd_string_exploded[1] == "2"):
        $pack = "UTME Registration – N10,000";
    endif;
    $response = "END You are about to purchase of {$pack}. Please wait while you are being redirected to make payment \n";
}
elseif( $ussd_string_exploded[0] == 8 && $level == 3 && $ussd_string_exploded[2] == "2" ){
    $response = "CON Your wallet balance: \n";
    $response .= "1. Proceed \n";
    $response .= "2. Cancel \n";
    $response .= "0. Main Menu \n";
}
//Final payment through walleting
elseif($ussd_string_exploded[0] == 8 && $level == 4 && $ussd_string_exploded[2] == "2"){
    if($ussd_string_exploded[1] == "1"):
        $pack = "UTME Result Checker – N8,500";
    elseif($ussd_string_exploded[1] == "2"):
        $pack = "UTME Registration – N10,000";
    endif;
    $response = "END You are about to purchase of {$pack}. Please wait while you are being redirected to make payment \n";
}
////////////////////////////////////////////////
////////////// End of JAMB ////////////////////
//////////////////////////////////////////////





///////////////////////////////////////////////////////////
////////////// Start of School registration //////////////
/////////////////////////////////////////////////////////
elseif ($text == "9") {
    $response  = "CON Select School/Partner \n";
    $response .= "1. Osogbo Intl Sch \n";
    $response .= "2. CAC-Oke Ola \n";
    $response .= "3. Osun Subeb Form \n";
    $response .= "4. LayLand Schools \n";
    $response .= "5. Ede Baptist Church \n";
    $response .= "6. Ede North Farm Credit \n";
    $response .= "7. Next \n"; 
    $response .= "0. Main Menu \n";
}
elseif($ussd_string_exploded[0] == 9 && $level == 2){
    $response = "CON Enter Payment ID or Surname: \n";
    $response .= "0. Main Menu \n";
}
elseif($ussd_string_exploded[0] == 9 && $level == 3){
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
    $response = "CON Confirm : ".$pack." \n";
    $response .= "<em>Select Payment Type</em> \n";
    $response .= "1. Bank USSD \n";
    $response .= "2. Wallet \n";
    $response .= "3. Debit Card \n";
    $response .= "4. New Debit Card \n";
    $response .= "0. Main Menu \n";
}
elseif( $ussd_string_exploded[0] == 9 && $level == 4 && $ussd_string_exploded[3] == "1" ){
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
elseif( $ussd_string_exploded[0] == 9 && $level == 5 && $ussd_string_exploded[3] == "1" ){
    $response = "CON Your account number: \n";
    $response .= "0. Main Menu \n";
}
//final payment using bank ussd
elseif($ussd_string_exploded[0] == 9 && $level == 6 && $ussd_string_exploded[3] == "1"){
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
elseif( $ussd_string_exploded[0] == 9 && $level == 4 && $ussd_string_exploded[3] == "2" ){
    $response = "CON Your wallet balance: \n";
    $response .= "1. Proceed \n";
    $response .= "2. Cancel \n";
    $response .= "0. Main Menu \n";
}
//Final payment through walleting
elseif($ussd_string_exploded[0] == 9 && $level == 5 && $ussd_string_exploded[3] == "2"){
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
header('Content-type: text/plain');
echo $response;

?>