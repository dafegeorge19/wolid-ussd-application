<?php
    function cpost($urlxxx, $dataxxx) {
        $ch = curl_init($urlxxx);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataxxx);
        $rxxxxxxxx = curl_exec($ch);
        curl_close($ch);
        return $rxxxxxxxx;
    }
    $vtu_code = "502";
    $apikey = "0b215eca15616eb4cc2bf5e38648c4123eccb295a7b508bb684e1c36a932d957";
    $serverid = "OKMTNAEKU";
    $sim = '1';
    $ref = rand(0000000000, 9999999999);
    $pin = "5555";
    //*502*1*4*07065738231*1*2*5555#
    $destinationNumber = "07065738231";
    $ussd = "*{$vtu_code}*1*4*{$destinationNumber}*1*2*{$pin}#";
    $postmtnair = [
        'server' => $serverid,
        'number' => $ussd,
        'ref' => $ref,
        'apikey' => $apikey,
        'sim'   => '1',
    ];
    $simhost_status = cpost('https://simhostng.com/api/ussd',$postmtnair);

    echo $simhost_status;

?>