<?php

class Wallet {
    
    function __construct() {
        
    }

    function getUser($phone){
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => API_URL."/single_read.php?phone_number={$phone}",
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
        return $response;
    }

    

    
}