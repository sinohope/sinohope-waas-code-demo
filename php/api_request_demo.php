<?php
// A demo for requesting Sinohope WaaS API

const APIVersion = '1.0.0';
const HeaderSignKey = 'BIZ-API-SIGNATURE';
const HeaderNonceKey = 'BIZ-API-NONCE';
const HeaderAPIKeyKey = 'BIZ-API-KEY';
const BaseURL = 'http://localhost:8000';
// const BaseURL = 'https://api.sinohope.com';

// your api secret key hex string
$myAPISecretHex = getenv('APISECRET_HEX');

$mySecret = hex2PrivateKey($myAPISecretHex);
$myAPIKeyHex = getenv('APIKEY_HEX');


function hex2PrivateKey($hexKey)
{
    if (!$hexKey) {
        throw new Exception("need environment variable APISECRET_HEX", 1);
    }
    $pkPem = hexToPEM($hexKey, "PRIVATE");

    $privateKeyResource = openssl_pkey_get_private($pkPem);
    return $privateKeyResource;
}

// convert hex public key string to PEM file content
function hexToPEM($hexKey, $type)
{
    $binaryKey = hex2bin($hexKey);
    $base64Key = base64_encode($binaryKey);

    // build PEM content
    $pemKey = "-----BEGIN " . $type . " KEY-----\n";
    $pemKey .= chunk_split($base64Key, 64, "\n");
    $pemKey .= "-----END " . $type . " KEY-----";

    return $pemKey;
}

/**
 * $path the request path
 * $requestData the data of request body
 * $timestamp milliseconds
 */
function SinohopeSignature($path, $requestData, $timestamp)
{
    global $myAPIKeyHex;
    global $mySecret;

    if (!$path) {
        throw new Exception("request path is required");
    }

    $bodyStr = "";
    if ($requestData) {
        $bodyStr = json_encode($requestData);
    }
    // Create the map of values
    $map = array(
        "data" => $bodyStr,
        "path" => $path,
        "timestamp" => $timestamp,
        "version" => APIVersion,
    );

    ksort($map);
    // concatenates all key values 
    $signMetaData = '';
    foreach ($map as $key => $value) {
        $signMetaData .= $key . $value;
    }

    // add public key hex, Generate the signMetaData
    $signData = $signMetaData . $myAPIKeyHex;
    // for debug
    error_log("signData:\n" . $signData, 4);

    // sign the data
    $signature = '';

    if ($mySecret !== false) {
        openssl_sign($signData, $signature, $mySecret, OPENSSL_ALGO_SHA256);
        // convert to hex
        $signature = bin2hex($signature);
    } else {
        throw new Exception("secret key is not provided");
    }

    return $signature;
}
// get current UNIX EPOCH timestamp, in milliseconds
function getTimestamp()
{
    $secTimestemp = microtime(true);
    $timestamp = round($secTimestemp * 1000);
    return $timestamp;
}

function setHeader($signature, $timestamp)
{
    global $myAPIKeyHex;

    $headerData = [
        HeaderAPIKeyKey . ":" . $myAPIKeyHex,
        HeaderNonceKey . ":" . $timestamp,
        HeaderSignKey . ":" . $signature,
        'Content-Type:application/json',
    ];

    return $headerData;
}

function doPost($path, $headers, $postData)
{
    $ch = curl_init(BaseURL . $path);
    if ($postData) {
        $jsonData = json_encode($postData);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, 1);

    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}

function bizPost($path, $data)
{
    try {
        $timestamp = getTimestamp();
        //gen signature
        $signature = SinohopeSignature($path, $data, $timestamp);
        // prepare headers
        $headers = setHeader($signature, $timestamp);
        // do post
        $response = doPost($path, $headers, $data);
        $responseData = json_decode($response);
    } catch (\Exception $e) {
        throw new Exception($e->getMessage());
    }
    return $responseData;
}

// get all vaults
function getVaults()
{
    $path = '/v1/waas/common/get_vaults';
    $data = null;
    return bizPost($path, $data);
}

// list all wallets of a vault
function listWallets($vaultId)
{

    $path = '/v1/waas/mpc/wallet/list_wallets';
    $data = [
        'vaultId' => $vaultId,
    ];
    return bizPost($path, $data);
}

// Example to get vaults and wallets, show the result by error_log
$res = getVaults();
error_log("getVaults result:\n" . json_encode($res), 4);
error_log("", 4);
if ($res->code == 200) {
    if (count($res->data) > 0) {
        $vaultList = $res->data[0]->vaultInfoOfOpenApiList;
        $firstVaultId = $vaultList[0]->vaultId;

        $walletRes = listWallets($firstVaultId);
        error_log("listWallets result:\n" . json_encode($walletRes), 4);
    }
}
?>