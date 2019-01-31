<?php

namespace App\Http\Controllers;

use config;
//use Illuminate\Routing\Controller;
use Monolog\Logger;
use Monolog\Processor\WebProcessor;
use Monolog\Formatter\LineFormatter; 
use Monolog\Handler\RotatingFileHandler;
use Monolog\Processor\IntrospectionProcessor;
use App\Http\Controllers\Utility\Utils;
use App\Http\Controllers\Utility\Validation;

abstract class BaseController
{
    /** @var Logger $logger */
    protected $logger;

    protected $version = 'web';

    protected function __construct($logName = 'JetEngage')
    {
        /**
         * Initialize logger
         */
        $this->logger = new Logger('JetEngage');
         
        $lineFormatter = new LineFormatter(
            env('LOG_FORMAT_CUSTOM'). PHP_EOL,
            env('LOG_DATE_FORMAT'),
            false,
            true
        );

        $logRotater = new RotatingFileHandler(
            \storage_path('logs/'.$this->version.'/'.date('Y-m-d').'/'.gethostname().'-'.$logName . '.log'),
            0,
            Logger::INFO,
            true,
            0777
        );

        $logRotater->setFormatter($lineFormatter);

        $clientIp = 'REMOTE_ADDR';

        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $clientIp = 'HTTP_CLIENT_IP';
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $clientIp = 'HTTP_X_FORWARDED_FOR';
        }

        $webProcessor = new WebProcessor(null, [
            'ip' =>  $clientIp,
            'protocol' => 'SERVER_PROTOCOL',
            'url' => 'REQUEST_URI',
            'http_method' => 'REQUEST_METHOD',
            'server' => 'SERVER_NAME',
            'referrer' => 'HTTP_REFERER',
        ]);

        $introspection = new IntrospectionProcessor;

        $this->logger->pushProcessor($webProcessor);
        $this->logger->pushProcessor($introspection);

        $this->logger->pushHandler($logRotater);
    }

    /**
     * Decrypt Request Payload
     *
     * @param arrya $payload
     */
    protected function decryptPayload($payload) //: array
    {
        $payloadEncrypt = env('DECRYPT_API_PAYLOAD', 1);

        if ($payloadEncrypt == 0) {

            /*
            if(defined('Allowed_Countries')) {
                if(isset($payload['locale']['country']) && $payload['locale']['country'] !== '') {
                    if(! in_array(strtoupper($payload['locale']['country']), Allowed_Countries)) {
                        $payload['locale']['actual_country'] = $payload['locale']['country'];
                        $payload['locale']['country'] = Default_Country ?? 'DE';
                    } else {
                        $payload['locale']['actual_country'] = $payload['locale']['country'];
                    }
                }
            }
            */

            $response = [
                'code'    => 200,
                'status'  => 'Success',
                'message' => 'Success',
                'data'    =>  $payload
            ];

            $this->logger->info(Utils::json($payload), $response);

            return $response;
        }

        $decriptPayload = SecureUtils::payloadDecrypt($payload);

        if ($decriptPayload['code'] !== 200) {
            $this->logger->error(Utils::json($payload), $decriptPayload);

            return $decriptPayload;
        }

        if (Validation::isJson($decriptPayload['data']) === false) {
            $error = [
                'code'    => 400,
                'status'  => 'Failed',
                'message' => 'Bad Request. Invalid or malformed JSON'
            ];
            $this->logger->error(Utils::json($payload), $error);

            return $error;
        }

        $params = Utils::jsonDecode($decriptPayload['data'], true);

        if (!isset($params) || empty($params)) {
            $error = [
                'code'    => 400,
                'status'  => 'Failed',
                'message' => 'Bad Request. Invalid payload data.'
            ];
            $this->logger->error(Utils::json($payload), $error);

            return $error;
        }

        /*
        if (defined('Allowed_Countries')) {
            if (isset($params['locale']['country']) && $params['locale']['country'] !== '') {
                if (! in_array(strtoupper($params['locale']['country']), Allowed_Countries)) {
                    $params['locale']['actual_country'] = $params['locale']['country'];
                    $params['locale']['country'] = Default_Country ?? 'DE';
                } else {
                    $params['locale']['actual_country'] = $params['locale']['country'];
                }
            }
        }
        */

        $response = [
            'code'    => 200,
            'status'  => 'Success',
            'data'    =>  $params,
            'message' => 'Success'
        ];
        $this->logger->info(Utils::json($payload), $response);

        return $response;
    }
}
