<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
/*** Encryption tests script.**/
/*** Require Library*/
require_once 'CoralPayPGPEncryption.php';

/**
 * Tests encryption abilities of Crypt_GPG.
 *
 * @category  Encryption
 * @package   Crypt_GPG
 * @author    Oseme Odigie <oseme.odigie@coralpay.com>
 * @copyright 2018-2019 coralpay
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 * @link      http://pear.php.net/package/Crypt_GPG
 */
class EncryptTestCase
{
    // private $gpg;
    // // private $amount;

    // public function __construct($amount) {
    //     $this->testingEncrypt($amount);
    //     // $this->testingDecrypt();
    // }

    /**
     * Testing the listing of the keys and GPG information
     * @group string
     */
    // public function getInfo()
    // {
    //     $options_array = array();
    //     $this->gpg = new CoralPayPGPEncryption($options_array);
        

    //     $encryptedData = $this->gpg->getKey($data, $keyId);
        
    //     print "\n\n";
    //     print_r($encryptedData . PHP_EOL);
    //     print "\n\n";
    // }

    /**
     * Testing the Encrypt part of the function
     * @group string
     */
    public function testingEncrypt($amount)
    {
        $options_array = array();
        
        $this->gpg = new CoralPayPGPEncryption($options_array);

        $data = '{"RequestHeader":{"Username":"beespeed","Password":"1315052721@003#7"},"RequestDetails":{"Amount":'.$amount.',"MerchantId":"1057BEE10000001","TerminalId":"1057BEE1","Channel":"USSD","TransactionType":"0"}}';

        $keyId = "tobiakintaro@yahoo.co.uk";

        $encryptedData = $this->gpg->encryptRequest($data, $keyId);

        return $encryptedData;
    }

    /**
     * Testing the Decrypt part of the function
     * @group string
     */
    public function testingDecrypt($response)
    {
        $options_array = array();
        $this->gpg = new CoralPayPGPEncryption($options_array);
        $keyId = 'renownjosimar@gmail.com';
        $passphrase = 'Frisky667@';
        $decryptedData = $this->gpg->decryptResponse($response, $keyId, $passphrase);
        return $decryptedData;
    }
}

$new = new EncryptTestCase();

?>
