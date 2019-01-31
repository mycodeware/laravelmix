<?php

namespace App\Http\Controllers\Utility;

use App\Http\Controllers\Utility\Utils;

class SecureUtils
{
    /**
     * Decrypt API Payload
     * @param array $params
     * @return array
     */
    public static function payloadDecrypt($data)
    {
        $payloadValidation = static::validatePayload($data);

        if ($payloadValidation['code'] !== 200) {
            return $payloadValidation;
        }

        $cipherData = $data['param1'];
        $rsaEncryptedKey = $data['param2'];
        $hmacHash = $data['param3'];

        if (static::hashValidation($cipherData, $hmacHash) === false) {
            return [
                "code" => 400,
                "status" => "failed",
                "message" => "Hash Validation failed."
            ];
        }

        $encryptionKey = static::RSADecrypt(base64_decode($rsaEncryptedKey));

        if ($encryptionKey['code'] !== 200) {
            return $encryptionKey;
        }

        $plainTextData = static::decryptData($cipherData, $encryptionKey['data']);

        if ($plainTextData !== null) {
            return [
                "code" => 200,
                "status" => "success",
                "message" => "success",
                "data" => $plainTextData
            ];
        }

        return [
            "code" => 400,
            "status" => "failed",
            "message" => "Sometnig went wrong. Please try again.",
            "error_message" => "AES decryption failed."
        ];
    }

    /**
     * Validate Payload
     * @param array $params
     * @return array
     */
    public static function validatePayload(array $params) : array
    {
        if (! isset($params['param1']) || $params['param1'] === "") {
            return [
                "code" => 601,
                "status" => "failed",
                "message" => "Missing param1."
            ];
        }

        if (! isset($params['param2']) || $params['param2'] === "") {
            return [
                "code" => 601,
                "status" => "failed",
                "message" => "Missing param2."
            ];
        }

        if (! isset($params['param3']) || $params['param3'] === "") {
            return [
                "code" => 601,
                "status" => "failed",
                "message" => "Missing param3."
            ];
        }

        return [
            "code" => 200,
            "status" => "success",
            "message" => "valid locale"
        ];
    }

    /**
     * HMAC SHA-256 Hash Validation
     *
     * @param string $cipherText
     * @return string $plaintext
     */
    public static function hashValidation($data, $hash)
    {
        $hashKey = env('HMAC_HASH_KEY');
        $hashAlgo = env('HMAC_HASH_ALGO');

        $generateHash = hash_hmac($hashAlgo, $data, $hashKey);

        if ($generateHash !== $hash) {
            return false;
        }

        return true;
    }

    /**
     * Open SSL RSA Decryption
     *
     * @param string $cipherText
     * @return string $plaintext
     */
    public static function RSADecrypt($cipherData)
    {
        while(openssl_error_string() !== false);

        $fileHander = fopen(base_path().'/crt/private.pem', 'r');

        $privateKey = fread($fileHander, 8192);
        fclose($fileHander);

        if(! openssl_private_decrypt(
            $cipherData,
            $decryptedKey,
            $privateKey,
            OPENSSL_PKCS1_OAEP_PADDING
        )) {
            return [
                'code' => 400,
                'status' => 'failed',
                'message' => 'RSA Decryption ERROR',
                'error_message' => "RSA Decryption ERROR : ".openssl_error_string()
            ];
        }

        return [
            'code' => 200,
            'status' => 'success',
            'message' => 'success',
            'data' => $decryptedKey
        ];
    }

    /**
     * Open SSL Decryption (AES-256-CBC)
     *
     * @version 1.1
     * @param string $cipherText
     * @return string $plaintext
     */
    public static function decryptData($cipherText, $decriptionKey)
    {
        try {

            //Get Key
            $key = $decriptionKey;

            //Get Algo from Credentials file
            $algo = env('CIPHER_ALGO');

            if (strlen($cipherText) <= 32) {
                return null;
            }

            $iv = substr($cipherText, 0, 32);

            $cipherText = substr($cipherText, 32);

            if ($cipherText == '') {
                return null;
            }

            //Convert $iv hex to bin
            $iv = hex2bin($iv);

            //$cipherText Hex decode
            $cipherText = hex2bin($cipherText);

            // Decrypt $data with key and iv
            $plaintext = openssl_decrypt(
                $cipherText,
                $algo,
                $key,
                OPENSSL_RAW_DATA,
                $iv
            );

            return $plaintext;

        } catch (\Throwable $exception) {
            return null;
        }
    }

    /**
     * Open SSL RSA Encrypt
     *
     * @param string $cipherText
     * @return string $plaintext
     */
    public static function RSAEncrypt($data)
    {
        while(openssl_error_string() !== false);

        $fileHander = fopen(base_path().'/crt/private.pem', 'r');
        $publicKey = fread($fileHander, 8192);
        fclose($fileHander);

        if (! openssl_public_encrypt(
            $data,
            $encryptedKey,
            $publicKey
            ,$padding = OPENSSL_PKCS1_OAEP_PADDING
        )) {
            return [
                'code' => 400,
                'status' => 'failed',
                'message' => 'RSA Encryption ERROR',
                'error_message' => "RSA Decryption ERROR : ".openssl_error_string()
            ];
        }

        return [
            'code' => 200,
            'status' => 'success',
            'message' => 'success',
            'data' => $encryptedKey
        ];
    }

    /**
     * Open SSL Encryption
     *
     * @version 1.1
     * @param int|string $data
     * @return string $combinedCipher
     */
    public static function encryptData($data, $encriptionKey)
    {
        try {
            //Get Key from Credentials file
            $key = $encriptionKey;

            //Get Algo from Credentials file
            $algo = CIPHER_ALGO;

            // Generates cryptographically secure pseudo-random bytes for Initialization Vector
            $iv = random_bytes(openssl_cipher_iv_length($algo));

            // Encrypt $data with key and iv
            $cipherText = openssl_encrypt(
                $data,
                $algo,
                $key,
                OPENSSL_RAW_DATA,
                $iv
            );

            //Convert $iv bin to hex
            $iv = bin2hex($iv);

            //$cipherText encoded in hex
            $cipherText = bin2hex($cipherText);

            //Append Iv and then cipherText append the concatenated
            $combinedCipher = $iv.$cipherText;

            return $combinedCipher;
        } catch (\Throwable $exception) {
            return null;
        }
    }
}
