<?php
// A simple callback server demo, the main purpose is to show how to verify the signature.
// for the details of the callback request, refer to : https://docs.sinohope.com/docs/develop/mpc-waas-api/callback-ap-is

const APIVersion = '1.0.0';
const HeaderSignKey = 'BIZ-API-SIGNATURE';
const HeaderNonceKey = 'BIZ-API-NONCE';
const HeaderAPIKeyKey = 'BIZ-API-KEY';

// you should get your sinohope callback API Key from sinohope WaaS web console page.
$sinohopeAPIKeyHex = getenv('APIKEY_HEX');
$sinohopeAPIKey = hex2PublicKey($sinohopeAPIKeyHex);

$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// transaction notify callback
if ($method == 'POST') {
    // get the request JSON body
    $body = file_get_contents('php://input');
    $data = json_decode($body, true);
    $headers = getallheaders();

    $ok = false;
    try {
        $ok = verifySign($uri, $headers, $body);
    } catch (\Exception $e) {
        error_log("verify signature failed: \n" . $e->getMessage());
    }
    if (!$ok) {
        header('Content-Type: application/json');
        echo json_encode(['code' => 401, 'message' => ' Invalid request']);
        return;
    }

    if ($uri == '/v1/call_back/transaction/notify') {
        // your business logics...
        // return success response
        header('Content-Type: application/json');
        echo json_encode(['code' => 200, 'requestId' => $data["requestId"], 'message' => '']);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['code' => 200, 'message' => '']);
    }

} else {
    error_log($method, 4);
    // for bad request
    header('HTTP/1.1 400 Bad Request');
    header('Content-Type: application/json');
    echo json_encode(['code' => 400, 'message' => 'Bad Request']);
}

/**
 * verify the callback request signature
 * $path the request path
 * $callBackHeaderData all header data
 * $callBackPostData the request body
 */
function verifySign($path, $allHeaders, $callBackPostData)
{
    global $sinohopeAPIKeyHex;
    global $sinohopeAPIKey;

    if (!$allHeaders) {

        throw new Exception("callback header data null");
    }
    $callBackHeaderData = [];
    foreach ($allHeaders as $key => $value) {
        $callBackHeaderData[strtoupper($key)] = $value;
    }
    $bodyStr = $callBackPostData;
    if (!$callBackPostData) {
        $bodyStr = "";
    }
    // check required headers
    if (!isset($callBackHeaderData[HeaderSignKey]) || !isset($callBackHeaderData[HeaderNonceKey]) || !isset($callBackHeaderData[HeaderAPIKeyKey])) {
        error_log("no required headers", 4);
        foreach ($callBackHeaderData as $key => $value) {
            error_log('=' . $key . '=', 4);
        }
        return false;
    }

    try {
        // try verify the signature
        $callBackSign = $callBackHeaderData[HeaderSignKey] ?: '';
        $nonce = $callBackHeaderData[HeaderNonceKey] ?: '';
        $apikey = $callBackHeaderData[HeaderAPIKeyKey] ?: '';

        // check the api key first
        if (strnatcasecmp($apikey, $sinohopeAPIKeyHex) != 0) {
            throw new Exception("illegal api key, want:\n" . $sinohopeAPIKeyHex . "\ngot:\n" . $apikey);
        }
        // build signData
        $map = array(
            "data" => $bodyStr,
            "path" => $path,
            "timestamp" => $nonce,
            "version" => APIVersion,
        );

        ksort($map);

        $signMetaData = '';
        foreach ($map as $key => $value) {
            $signMetaData .= $key . $value;
        }
        //add public key, Generate the signMetaData
        $signData = $signMetaData . $sinohopeAPIKeyHex;

        // for debug
        error_log($signData, 4);

        // convert the signature to bin
        $callBackSignBin = hex2bin($callBackSign);
        $res = openssl_verify($signData, $callBackSignBin, $sinohopeAPIKey, OPENSSL_ALGO_SHA256);

        //日志记录
        $logData = [
            'operational_data' => [
                "callback_header_data" => $callBackHeaderData,
                "callback_post_data" => $callBackPostData,
                "path" => $path,
                "version" => APIVersion,
                "callback_sign" => $callBackSign,
                "openssl_verify_res" => $res
            ],
        ];

        error_log("request and verify result:", 4);
        error_log(json_encode($logData), 4);

        //对比签名
        if (!$res) {
            throw new Exception("verify signature failed, result: " . $res);
        }

    } catch (\Exception $e) {
        throw new Exception($e->getMessage());

    }

    return TRUE;

}

// convert hex public key (API  Key) to openssl public key
function hex2PublicKey($hexPK)
{
    if (!$hexPK) {
        throw new Exception("need environment variable APIKEY_HEX", 1);
    }
    $pkPem = hexToPEM($hexPK, "PUBLIC");

    $publicKeyResource = openssl_pkey_get_public($pkPem);
    return $publicKeyResource;
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
?>