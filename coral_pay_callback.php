<?php
    $callback_response = file_get_contents('php://input');
    $obj = json_decode($callback_response, TRUE);
    if($obj["responseCode"] == "00"){
        echo "Your transaction was successful, Thanks for using Wolid.";
        return;
    }else{
        echo $obj["responsemessage"];
        return;
    }
?>