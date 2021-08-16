<?php
$bundle = "40MB";
function cpost($urlxxx, $dataxxx)
{
    $ch = curl_init($urlxxx);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $dataxxx);
    $rxxxxxxxx = curl_exec($ch);
    curl_close($ch);
    return $rxxxxxxxx;
}
$apikey = "0b215eca15616eb4cc2bf5e38648c4123eccb295a7b508bb684e1c36a932d957";
$serverid = "OKMTNAEKU";
$sim = '1';
$number = "07065738231";

if($bundle == '40MB'){
    $mtn = "502 1 4 {$number} 1 2 5555";
    $md = "1";
}
if ($bundle == '500MB') {
    $mtn = 'SMEB ' . $number . ' 500 1313';
    $md = '1';
}
if ($bundle == '1GB') {
    $mtn = 'SMEC ' . $number . ' 1000 1313';
    $md = '1';
}
if ($bundle == '2GB') {
    $mtn = 'SMED ' . $number . ' 2000 1313';
    $md = '1';
}
if ($bundle == '3GB') {
    $mtn = 'SMEF ' . $number . ' 3000 1313';
    $md = '1';
}
if ($bundle == '5GB') {
    $mtn = 'SMEE ' . $number . ' 5000 1313';
    $md = '1';
}

$postmtndata = [
    'server' => $serverid,
    'apikey' => $apikey,
    'message' => $mtn,
    'number' => '131',
    'ref' => $ref,
    'sim'   => $sim,
];

$response = cpost('https://simhostng.com/api/sms', $postmtndata);

echo $response;
