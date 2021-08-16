<?php
require_once "constants.php";
$request = new HTTP_Request2();
$request->setUrl('{{BASE_BILL_PAYMENT}}/bills');
$request->setMethod(HTTP_Request2::METHOD_POST);
$request->setConfig(array(
  'follow_redirects' => TRUE
));
$request->setHeader(array(
  'Authorization' => "Bearer {BASE_SECRET_KEY}",
  'Content-Type' => 'application/json'
));
$request->setBody('{\n	"country": "NG",\n	"customer": "+23490803840303",\n	"amount": 500,\n	"recurrence": "ONCE",\n	"type": "AIRTIME",\n	"reference": "9300049404444"\n }');
try {
  $response = $request->send();
  if ($response->getStatus() == 200) {
    echo $response->getBody();
  }
  else {
    echo 'Unexpected HTTP status: ' . $response->getStatus() . ' ' .
    $response->getReasonPhrase();
  }
}
catch(HTTP_Request2_Exception $e) {
  echo 'Error: ' . $e->getMessage();
}