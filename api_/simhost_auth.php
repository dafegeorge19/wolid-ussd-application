<?php

$apikey = "6ebea91dfe54a937459d8c100553f510814d19e285139a2d634c3f32d853a113";
$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, "https://simhostng.com/api/{$apikey}");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($ch, CURLOPT_HEADER, TRUE);

curl_setopt($ch, CURLOPT_POST, TRUE);

$fields = [
  "username" => "dafegeorge19",
  "password" => "Frisky667@",
];
curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);

curl_setopt($ch, CURLOPT_HTTPHEADER, array(
  "Content-Type : application/json",
));

$response = curl_exec($ch);
$info = curl_getinfo($ch);
curl_close($ch);

echo json_encode($response);

// var_dump($info["http_code"]);
// var_dump($response);