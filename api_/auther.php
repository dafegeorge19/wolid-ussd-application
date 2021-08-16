<?php
            header('Content-type: text/html; charset=utf-8');

                // URL to fetch
                $url = "http://192.168.43.2:2000/user/service/login";
            // Setting the HTTP Request Headers
                $User_Agent = 'Mozilla/5.0 (Windows NT 6.1; rv:60.0) Gecko/20100101 Firefox/60.0';
                $request_headers[] = 'X-picturemaxx-api-key: key';
                $request_headers[] = 'Contect-Type:text/html';
                $request_headers[] = 'Accept:text/html';
                $request_headers[] = 'Accept: application/json';
                $request_headers[] = 'Content-type: application/json';
                $request_headers[] = 'Accept-Encoding:  gzip, deflate, identity';
                $request_headers[] = 'Expect: ';                   

                $dataj = array (
                'password' => '123456',
                'username' => 'dealer1@gmail.com',
                );
                 $data_json = json_encode($dataj);
                 $request_headers[] = 'Content-Length: ' . strlen($data_json);


              $ch = curl_init($url);
              // Set the url      

        curl_setopt( $ch, CURLOPT_URL, $url );
        curl_setopt($ch, CURLOPT_USERAGENT, $User_Agent);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $request_headers);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_ENCODING, "");

                // Execute
                $result = curl_exec($ch);
              $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
 // Performs the Request, with specified curl_setopt() options (if any).
                //$data = json_decode( file_get_contents( 'php://input' ), true );

                // Closing
        curl_close($ch);
$data = json_decode($result, true);
if ($code == 200) {
    $result = json_decode($result, true);
    print_r($result);
} else {
    echo 'error ' . $code;
}

print_r($result);
print_r($data);
