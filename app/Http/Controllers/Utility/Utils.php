<?php

namespace App\Http\Controllers\Utility;

class Utils
{
    /** @var array $credentials */
    public static $credentials;

    /**
     * Method to print any output in pretty way
     * @param mixed
     * @return void
    */
    public static function printData($obj, $dump = false)
    {
        echo "<pre>";
        if ($dump === true) {
            var_dump($obj);
        } else {
            print_r($obj);
        }
        echo "</pre>";
    }

    /**
     * Get Document Root
     *
     * @return string
     */
    public static function getDocumentRoot(): string
    {
        $docRoot = dirname(__DIR__).'/';

        if (php_sapi_name() !== 'cli') {
            $docRoot = static::getServerHeader('DOCUMENT_ROOT');
        }

        return $docRoot;
    }

    /**
     * Convert input into Json Object
     * @param mixed $json
     * @param int $options
     * @return string
     */
    public static function json($json, $options = 0)
    {
        if (is_array($json)) {
            return json_encode($json, $options);
        }
        return json_encode(array($json), $options);
    }

    /**
     * Convert Json Object into array or \StdClass
     * @param mixed
     * @param bool
     * @return array|object|null
     */
    public static function jsonDecode($json = null, $option = false)
    {
        if (null === $json) {
            return $json;
        }

        return json_decode($json, $option);
    }

    /**
    * Parse Ini Files
    * @param string Ini File Path
    * @param bool
    * @return array
    */
    public static function parseIniFiles($file, $processSection = true): array
    {
        return parse_ini_file($file, $processSection);
    }

    /**
     * Get specific header from Server Header
     * @param string
     * @return string|false
     */
    public static function getServerHeader($header = null)
    {
        if (php_sapi_name() !== 'cli' && php_sapi_name() !== 'phpdbg') {
            if ($header === null) {
                return $_SERVER;
            }
            return $_SERVER[$header] ?? null;
        }
        return false;
    }

    /**
     * Encode input string in base64
     * @param string
     * @return string base64 encoded
     */
    public static function encode($text)
    {
        return base64_encode($text);
    }

    /**
     * Decode cipher string in original string
     * @param string base64 encoded
     * @return string
     */
    public static function decode($text)
    {
        return base64_decode($text);
    }

    /**
     * Remove url from string
     * @param string $string
     * @param string $replacement
     * @return string
     */
    public static function cleanUrl($string, $replacement = ""): string
    {
        $pattern = "/[a-zA-Z]*[:\/\/]*[A-Za-z0-9\-_]+\.+[A-Za-z0-9\.\/%&=\?\-_]+/i";
        return preg_replace($pattern, $replacement, $string);
    }

    /**
     * Replace +/= to -_,
     * @param string $inputStr
     * @return string
     */
    public static function base64UrlEncode($inputStr): string
    {
        return strtr(base64_encode($inputStr), '+/=', '-_,');
    }

    /**
     * Replace -_, to +/=
     * @param string $inputStr
     * @return string
     */
    public static function base64UrlDecode($inputStr): string
    {
        return base64_decode(strtr($inputStr, '-_,', '+/='));
    }

    /**
     * Write Base64 Encode string in File
     * and convert to image
     * @param string
     * @param string
     * @return int|bool
     */
    public static function base64ToImage($outputFile, $base64String)
    {
        $data = explode(',', $base64String);

        $ifp = file_put_contents($outputFile, self::decode($data[1]));

        return $ifp;
    }

    /**
     * Convert 1000 t0 1k/M
     * @param string
     * @return string
     */
    public static function restyleText($input): string
    {
        $input = number_format($input);
        $inputCount = substr_count($input, ',');

        if ($inputCount == 0) {
            return $input;
        }

        if ($inputCount == '1') {
            return substr($input, 0, -4).'K';
        } elseif ($inputCount == '2') {
            return substr($input, 0, -8).'M';
        } elseif ($inputCount == '3') {
            return substr($input, 0, -12).'B';
        }

        return "";
    }

    /**
     * Convert 1000 t0 1k/M
     * @param string
     * @return string
     */
    public static function numberAbbreviation($number)
    {
        $abbrevs = array(12 => "T", 9 => "B", 6 => "M", 3 => "K", 0 => "");
        foreach ($abbrevs as $exponent => $abbrev) {
            if ($number >= pow(10, $exponent)) {
                $displayNum = $number / pow(10, $exponent);
                $decimals = ($exponent >= 3 && round($displayNum) < 100) ? 1 : 0;
                return number_format($displayNum, $decimals) . $abbrev;
            }
        }

        return $number;
    }

    /**
     * Check if value needle exists in haystack (associative array)
     * @param string $needle
     * @param array $haystack
     * @param bool $strict
     * @return bool
     */
    public static function inArrayR($needle, array $haystack, $strict = false): bool
    {
        foreach ($haystack as $item) {
            if (($strict ? $item === $needle : $item == $needle) ||
                (is_array($item) && static::inArrayR($needle, $item, $strict))
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get current Page Name
     * @return string
     */
    public static function getPageName(): string
    {
        return strtolower(ucfirst(pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME)));
    }

    /**
     * Trim array values at deepest level
     * @param mixed
     */
    public static function trimArrayValue(&$value)
    {
        $value = trim($value);
    }

    /**
     * Stri Script Tags array values at deepest level
     * @param mixed
     */
    public static function stripScriptTags(&$value)
    {
        $value = filter_var($value, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
    }

    /**
     * Get Client IP
     *
     * @return string
     */
    public static function getClientIp()
    {
        if (php_sapi_name() !== 'cli' && php_sapi_name() !== 'phpdbg') {
            $client  = $_SERVER['HTTP_CLIENT_IP'] ?? '';
            $forward = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '';
            $remote  = $_SERVER['REMOTE_ADDR'] ?? '';

            if (filter_var($client, FILTER_VALIDATE_IP)) {
                return $client;
            } elseif (filter_var($forward, FILTER_VALIDATE_IP)) {
                return $forward;
            }

            return $remote;
        }

        return '';
    }

    /**
     * Check folder exists or not
     * @param string $folder
     * @return bool
     */
    public static function folderExists($folder): bool
    {
        // Get canonicalized absolute pathname
        $path = realpath($folder);

        // If it exist, check if it's a directory
        return ($path !== false and is_dir($path)) ? $path : false;
    }

    /**
     * Validate locale
     * @param array $params
     * @return array
     */
    public static function validateLocale(array $params) : array
    {

        if (! isset($params['locale']) || empty($params['locale'])) {
            return [
                "code" => 601,
                "status" => "failed",
                "message" => "Missing App Locale."
            ];
        }

        if (! isset($params['locale']['version']) || empty($params['locale']['version'])) {
            return [
                "code" => 601,
                "status" => "failed",
                "message" => "Invalid App Version."
            ];
        }
        if (! isset($params['locale']['platform']) || empty($params['locale']['platform'])) {
            return [
                "code" => 601,
                "status" => "failed",
                "message" => "Invalid Platform."
            ];
        }

        if (! isset($params['locale']['language']) || empty($params['locale']['language'])) {
            return [
                "code" => 601,
                "status" => "failed",
                "message" => "Invalid Language."
            ];
        }

        if (! isset($params['locale']['country']) || empty($params['locale']['country'])) {
            return [
                "code" => 601,
                "status" => "failed",
                "message" => "Invalid Country."
            ];
        }

        /*
        if (! isset($params['locale']['segment'])) {
            return [
                "code" => 601,
                "status" => "failed",
                "message" => "Invalid Segment."
            ];
        }
        */

        if (
            strtolower($params['locale']['platform']) != 'android' &&
            strtolower($params['locale']['platform']) != 'windows' &&
            strtolower($params['locale']['platform']) != 'ios' &&
            strtolower($params['locale']['platform']) != 'web'
        ) {
            return [
                "code" => 601,
                "status" => "failed",
                "message" => "Invalid Platform."
            ];
        }

        return [
            "code" => 200,
            "status" => "success",
            "message" => "valid locale"
        ];
    }

    /**
     * Get Credentials from Ini File
     * @param string $fileName
     * @return array
     */
    public static function getPlatformDetailsFromIni($fileName): array
    {
        if (empty(static::$credentials)) {
            static::$credentials = static::parseIniFiles("$fileName.ini");

            return static::$credentials;
        }

        static::$credentials = array_merge(static::$credentials, static::parseIniFiles("$fileName.ini"));

        return static::$credentials;
    }

    /**
     * Get Specific Platform Detail
     * @param string|null $platform
     * @return array
     */
    public static function getPlatformDetail($platform = null)
    {
        if ($platform !== null) {
            return static::$credentials[ucfirst($platform)];
        }

        return static::$credentials;
    }

    /**
     * Parse Comment to replace backslash, newline characters
     *
     * @param string $comment
     * @return string
     */
    public static function parseComment(string $comment): string
    {
        $carryForwardRegexPattern = '/\\\\\\\\*r/';
        $newLineRegexPattern = '/\\\\\\\\*n/';
        $doubleQuoteRegexPattern = '/\\\\*"/';
        $forwardSlashRegexPattern = '/\\\\*\//';

        $str = preg_replace($carryForwardRegexPattern, "\r", $comment);
        $str = preg_replace($newLineRegexPattern, "\n", $str);
        $str = preg_replace($doubleQuoteRegexPattern, "\"", $str);
        $str = preg_replace($forwardSlashRegexPattern, "/", $str);

        return $str;
    }

    /**
     * Safe Base 64 Encode of url
     *
     * @param string
     * @return string
     */
    public static function urlSafeBase64Encode($value): string
    {
        $encoded = base64_encode($value);

        // Replace characters that cannot be included in a URL.
        return str_replace(array('+', '=', '/'), array('-', '_', '~'), $encoded);
    }

    /**
     * Get Size in Bytes
     * @param string $size
     * @return int
     */
    public static function getBytes(string $size): int
    {
        $size = trim($size);
        $last = strtolower($size[strlen($size)-1]);
        $size = (int) $size;

        switch ($last) {
            // The 'G' modifier is available since PHP 5.1.0
            case 'g':
                $size *= (1024 * 1024 * 1024); //1073741824
                break;
            case 'm':
                $size *= (1024 * 1024); //1048576
                break;
            case 'k':
                $size *= 1024;
                break;
        }

        return (int) $size;
    }

    /**
     * Time Ago
     *
     * @param string
     * @param bool
     * @return string
     */
    public static function timeAgo($datetime, $full = false)
    {
        $now = new \DateTime;
        $ago = new \DateTime($datetime);
        $diff = $now->diff($ago);

        if ($diff->d > 6) {
            return date('jS M y', substr($datetime, 1));
        }

        $diff->w = floor($diff->d / 7);
        $diff->d -= $diff->w * 7;

        $string = array(
            'y' => 'year',
            'm' => 'month',
            'w' => 'week',
            'd' => 'day',
            'h' => 'hr',
            'i' => 'min',
            's' => 'sec',
        );

        foreach ($string as $key => &$value) {
            if ($diff->$key) {
                $value = $diff->$key . ' ' . $value . ($diff->$key > 1 ? 's' : '');
            } else {
                unset($string[$key]);
            }
        }

        if (!$full) {
            $string = array_slice($string, 0, 1);
        }

        return $string ? implode(', ', $string) . ' ago' : 'just now';
    }

    /**
     * Retrieve error code from exception string
     *
     * @api
     * @version 1.0
     * @param string $exception
     * @return array
     */
    public static function parseExceptionString($exception)
    {
        $returnCode=[];
        $exceptionArray = explode('response:', $exception);

        $returnCode=self::jsonDecode($exceptionArray[1],true);

        if(is_array($returnCode)){
            return $returnCode;
        }

        return [];

    }

    /**
     * Write Custom header Log's
     *
     *@param array
     */
    public static function customLogger(array $params)
    {
        $commaSeparatedData = implode("#", $params);

        if (!file_exists(APP_ROOT.'logs')) {
            mkdir(APP_ROOT.'logs', 0777, true);
        }

        file_put_contents(APP_ROOT.'logs/YesFlixCustomLog-'.date('Y-m-d-H').'.log', $commaSeparatedData.PHP_EOL, FILE_APPEND);
    }

    public static function getSessionById($id)
    {
        return $_SESSION;
        $redisSessionId = static::decrypt($id);
        session_id($redisSessionId);
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        return $_SESSION;
    }

    /**
     * Generate Transaction Id
     * @param array
     * @return string
     */
    public static function getTransactionId(string $prefix, array $keys): string
    {
        $transactionId = $prefix;

        foreach ($keys as $key) {
            $transactionId .= $key;
        }

        $transactionId .= static::millitime();

        return $transactionId;
    }

    /**
     * Get seconds in millitime
     *
     * @return string
     */
    public static function millitime(): string
    {
        $microtime = microtime();
        $comps = explode(' ', $microtime);

        // Note: Using a string here to prevent loss of precision
        // in case of "overflow" (PHP converts it to a double)
        return sprintf('%d%03d', $comps[1], $comps[0] * 1000);
    }


    public static function isSSLOn(): bool
    {
        $isSSLOn = false;
        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) == 'https') {
            $isSSLOn = true;
        }
        return $isSSLOn;
    }
}
