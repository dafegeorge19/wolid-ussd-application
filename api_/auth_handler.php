<?php
$ch = curl_init();

// curl --location --request POST '192.168.43.2:2000/user/service/login' \
// --data-raw '{
//     "email": "serviceUser@topup",
//     "password": "123456"
// }'

curl_setopt($ch, CURLOPT_URL, "http://192.168.43.2:2000/user/service/login");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($ch, CURLOPT_HEADER, TRUE);

curl_setopt($ch, CURLOPT_POST, TRUE);

$fields = <<<EOT
{
  "username": "dealer1@gmail.com",
  "password": "123456",
}
EOT;
curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);

curl_setopt($ch, CURLOPT_HTTPHEADER, array(
  "Content-Type: application/json"
));

$response = curl_exec($ch);
$info = curl_getinfo($ch);
curl_close($ch);

echo json_encode($response);

// var_dump($info["http_code"]);
// var_dump($response);