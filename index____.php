<?php
// Reads the variables sent via POST
$sessionId   = $_POST["sessionId"];
$serviceCode = $_POST["serviceCode"];
$text = $_POST["text"];
$phonenumber= $_POST['MSISDN'];
//This is the first menu screen
if ( $text == "" ) {
    $response="CON Welcome! Select an Option \n";
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
else if ($text == "1") {
    $response  = "CON  Select network \n";
    $response .= "1. MTN \n";
    $response .= "2. GLO \n";
    $response .= "3. AIRTEL \n";
    $response .= "4. 9MOBILE \n";
    $response .= "0. Main Menu \n";
}
else if ($text == "1*1") {
    $response = "CON Enter amount \n";
    $response .= "0. Main Menu \n";
    $level = explode("*", $text);
    $level = $level[2]."*".$text;
}
else if ($text == "1*1*".$level) {
    $response = "CON Select beneficiary \n";
    $reponse .= "1. Self \n";
    $reponse .= "2. Third Party \n";
    $reponse .= "0. Main Menu \n";
}
else if ($text == "1*1*1*1") {
    $response = "CON Confirm Airtime: \n";
    $reponse .= "For: SELF \n";
    $reponse .= "Select Payment Type \n";
    $reponse .= "1. Bank USSD \n" ;
    $reponse .= "2. Wallet \n";
    $reponse .= "3. Debit Card \n";
    $reponse .= "4. New Debit Card \n";
    $reponse .= "0. Main Menu \n";
}
else if ($text == "1*1*1*1*1") {
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
else if ($text == "1*1*1*0") {
    $response = "END Your Table reservation for 2 has been canceled";
}
// Menu for a user who selects "2" from the second menu above
// Will be brought to this fourth menu screen
else if ($text == "1*2") {
    $response = "CON You are about to book a table for 4 \n";
    $response .= "Please Enter 1 to confirm \n";
}
// Menu for a user who selects "1" from the fourth menu screen
else if ($text == "1*2*1") {
    $response = "CON Table for 4 cost -N- 150,000.00 \n";
    $response .= "Enter 1 to continue \n";
    $response .= "Enter 0 to cancel";
}
else if ($text == "1*2*1*1") {
    $response = "END Your Table reservation for 4 has been booked";
}
else if ($text == "1*2*1*0") {
    $response = "END Your Table reservation for 4 has been canceled";
}
// Menu for a user who enters "3" from the second menu above
// Will be brought to this fifth menu screen
else if ($text == "1*3") {
    $response = "CON You are about to book a table for 6 \n";
    $response .= "Please Enter 1 to confirm \n";
}
// Menu for a user who enters "1" from the fifth menu
else if ($text == "1*3*1") {
    $response = "CON Table for 6 cost -N- 250,000.00 \n";
    $response .= "Enter 1 to continue \n";
    $response .= "Enter 0 to cancel";
}
else if ($text == "1*3*1*1") {
    $response = "END Your Table reservation for 6 has been booked";
}
else if ($text == "1*3*1*0") {
    $response = "END Your Table reservation for 6 has been canceled";
}
// Menu for a user who enters "4" from the second menu above
// Will be brought to this sixth menu screen
else if ($text == "1*4") {
    $response = "CON You are about to book a table for 8 \n";
    $response .= "Please Enter 1 to confirm \n";
}
// Menu for a user who enters "1" from the sixth menu
else if ($text == "1*4*1") {
    $response = "CON Table for 8 cost -N- 250,000.00 \n";
    $response .= "Enter 1 to continue \n";
    $response .= "Enter 0 to cancel";
}
else if ($text == "1*4*1*1") {
    $response = "END Your Table reservation for 8 has been booked";
}
else if ($text == "1*4*1*0") {
    $response = "END Your Table reservation for 8 has been canceled";
}
//echo response
header('Content-type: text/plain');
echo $response
?>