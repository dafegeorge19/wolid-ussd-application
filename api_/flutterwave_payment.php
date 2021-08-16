<?php

class FlutterPayment{

    function AllBanks(){
        $key = "FLWSECK_TESTd227a1d52741";
        $secret_key = "FLWSECK_TEST-b947e7e0ab1712f212f108b343107dfd-X";
        $public_key = "FLWPUBK_TEST-21aa76d64f8121e5fd4548c0ee6a753d-X";
        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => "https://api.flutterwave.com/v3/banks/NG",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => array(
            "Authorization: Bearer {$secret_key}"
        ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;
    }

    function MakePayment($amount, $bankCode, $phoneNumber, $whatToDo){
        $key = "FLWSECK_TESTd227a1d52741";
        // $secret_key = "FLWSECK_TEST-b947e7e0ab1712f212f108b343107dfd-X";
        $secret_key = "FLWSECK-8c42cc25bf7e761f4fa6b36bd0fb365f-X";
        $public_key = "FLWPUBK_TEST-21aa76d64f8121e5fd4548c0ee6a753d-X";

        $curl = curl_init();

        $ref = "MC~".$whatToDo;

        curl_setopt_array($curl, array(
        CURLOPT_URL => "https://api.flutterwave.com/v3/charges?type=ussd",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => json_encode([
                "tx_ref" => $ref,
                "account_bank" => $bankCode,
                "amount" => $amount,
                "currency" => "NGN",
                "email" => "customer@wolid.com",
                "phone_number" => $phoneNumber,
                "fullname" => "Wolid Customer",
                "redirect_url" => "https://wolid.herokuapp.com/api_/flutterwave_callback.php"
        ]),
        CURLOPT_HTTPHEADER => array(
            "Authorization: Bearer {$secret_key}",
            "Content-Type: application/json"
        ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;
    }
}
?>
