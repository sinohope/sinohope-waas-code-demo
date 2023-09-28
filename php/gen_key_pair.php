<?php

// generates an ECDSA key pair that matchs the Sinohope WaaS requiements.
function generateECDSAKeyPair() {
    $config = [
        "curve_name" => "prime256v1",
        "private_key_type" => OPENSSL_KEYTYPE_EC,
    ];

    $res = openssl_pkey_new($config);

    if (!$res) {
        die("Failed to generate private key");
    }

    // get private key, in PEM format
    openssl_pkey_export($res, $privateKey);

    // get publick key
    $details = openssl_pkey_get_details($res);
    $publicKey = $details["key"];

    return [$privateKey, $publicKey];
}

// convert a pem key to hexadecimal string
function pemToHex($pem) {
    $pem = preg_replace("/-----BEGIN (.*)-----/", "", $pem);
    $pem = preg_replace("/-----END (.*)-----/", "", $pem);
    $pem = str_replace("\n", "", $pem);
    $bin = base64_decode($pem);
    return bin2hex($bin);
}

list($privateKey, $publicKey) = generateECDSAKeyPair();

echo "Private Key (PEM):\n";
echo $privateKey . "\n\n";

echo "Private Key (Hex):\n";
echo pemToHex($privateKey) . "\n\n";

echo "Public Key (Hex):\n";
echo pemToHex($publicKey) . "\n";

?>
