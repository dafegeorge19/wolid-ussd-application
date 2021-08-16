<?php
// Reads the variables sent via POST
$sessionId   = $_POST["sessionId"];
$serviceCode = $_POST["serviceCode"];
$text = $_POST["text"];
//This is the first menu screen
if ( $text == "" ) {
    $response  = "CON Welcome! Select an Option \n";
    $response .= "1. Buy Airtime \n";
    $response .= "2. Buy Data \n";
    $response .= "3. Wallet \n";
    $response .= "4. Electricity Bill \n";
    $response .= "5. DSTV \n";
    $response .= "6. GoTV \n";
    $response .= "7. WAEC Checker \n";
    $response .= "8. Jamb Card \n";
    $response .= "9. Schools & Orgs \n";
}
// Menu for a user who selects '1' from the first menu
// Will be brought to this second menu screen
elseif ($text == "1") {
    require_once 'controllers/airtime-recharge.php';
}
//Menu for a user who selects '1' from the second menu above
// Will be brought to this third menu screen
elseif ($text == "2") {
    require_once 'controllers/data-bundle.php';
}
elseif ($text == "3") {
    require_once 'controllers/walleting.php';
}
elseif ($text == "4") {
    require_once 'controllers/electricity.php';
}
elseif ($text == "5") {
    require_once 'controllers/dstv.php';
}
elseif ($text == "6") {
    require_once 'controllers/gotv.php';
}
// Menu for a user who selects "1" from the fourth menu screen
elseif ($text == "7") {
    require_once 'controllers/waec.php';
}
elseif ($text == "8") {
    require_once 'controllers/jamb.php';
}
elseif ($text == "9") {
    require_once 'controllers/school-payment.php';
}
elseif ($text == "1*4*1*0") {
    $response = "END Your Table reservation for 8 has been canceled";
}
//echo response
header('Content-type: text/plain');
echo $response
?>