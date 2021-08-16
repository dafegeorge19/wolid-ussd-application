<?php
$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, "http://197.253.19.76:1880/api/v1/vas/cabletv/validation");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($ch, CURLOPT_HEADER, TRUE);

curl_setopt($ch, CURLOPT_POST, TRUE);

$fields = <<<EOT
{
"service": "multichoice",
"channel": "MOBILE",
"type": "DSTV",
"account": "7029697853"
}
EOT;
curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);

curl_setopt($ch, CURLOPT_HTTPHEADER, array(
"Content-Type: application/json",
"+: signature: `{Request Signature}` (string)"
));

$response = curl_exec($ch);
$info = curl_getinfo($ch);
curl_close($ch);

var_dump($info["http_code"]);
var_dump($response);