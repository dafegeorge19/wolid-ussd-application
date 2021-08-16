<?php
#We obtain the data which is contained in the post url on our server.

$text=$_GET['USSD_STRING'];
$phonenumber=$_GET['MSISDN'];
$serviceCode=$_GET['serviceCode'];
#we explode the text using the separator ‘*’ which will give us an array.
$level = explode("*", $text);
//check to see of the text variable has data to avoid errors.
if (isset($text)) {
//    The first request will have an empty text field and thus should show the welcome screen to the user. Note the user of CON keyword.

if ( $text == "" ) {
    $response="CON Welcome to the registration portal.\nPlease enter you full name";
 }
if(isset($level[0]) && $level[0]!="" && !isset($level[1])){
    $response="CON Hi ".$level[0].", enter your ward name";

 }
 else if(isset($level[1]) && $level[1]!="" && !isset($level[2])){
        $response="CON Please enter you national ID number\n";
}
    else if(isset($level[2]) && $level[2]!="" && !isset($level[3])){
        //Save data to database
        $data=array(
            'phonenumber'=>$phonenumber,
            'fullname' =>$level[1],
            'electoral_ward' => $level[2],
            'national_id'=>$level[3]
        );
//Insert the values into the db SOMEWHERE HERE!!
//        We end the session using the keyword END.
        $response="END Thank you ".$level[1]." for registering.\nWe will keep you updated";
 }
//Also note that we echo the response for every successful request.
    header('Content-type: text/plain');
 echo $response;
}
?>