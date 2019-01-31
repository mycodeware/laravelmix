<?php

namespace App\Http\Controllers;

use Validator;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Sql\Configuration;
use Illuminate\Support\Facades\Redis;
use App\Http\Controllers\Utility\Utils;
use App\Http\Controllers\BaseController;
/**
 * IpLocale Class
 *
 * @library         JetEngage
 *
 * @license         <add Licence here>
 * @link            www.jetsynthesys.com
 * @author          Imran Khan <imran.khan@jetsynthesys.com>
 * @since           Jan 18, 2019
 * @copyright       2016 Jetsynthesys Pvt Ltd.
 * @version         1.0
 */
class IpLocale extends BaseController
{
     /**
     * Default Meesage Language
     */
    protected $defaultMessageLanugage;

    public function __construct()
    {
        parent::__construct('ip_locale');
    }

    /**
     * Get IpLocale
     *
     * @return array
     */
    public function get(Request $request)
    {
        $params = [];

        $params = $request->all();

        $ip = $this->getIpAddress();

        try {
            if (!is_null($ip)) {

                $httpClient = new \GuzzleHttp\Client();

                $response = $httpClient->request(
                    'POST',
                    env('IP2LOCATION_URL'),
                    [
                        'json'    => [
                            'ip' => $ip
                        ]
                    ]
                );

                $output = Utils::jsonDecode($response->getBody()->getContents(), true);

                if (isset($output['Versions']) && ! empty($output['Versions'])) {

                    $response = [
                        'code'   => 200,
                        'status' => 'success',
                        'message'=> 'success',
                        'data' => [
                            'Versions' => $output['Versions']
                        ]
                    ];

                    $this->logger->info(Utils::json($params), $response);

                    return $response;
                }

                $error = [
                    'code'         => 600,
                    'status'       => 'failed',
                    'message'      => ErrorMessageConstants::ERROR_MESSAGES[$this->defaultMessageLanugage]['somethingWrong'],
                    'error_message' => $response->getBody()->getContents()
                ];

                $this->logger->error(Utils::json($params), $error);

                return $error;

            }

            $error = [
                'code'         => 600,
                'status'       => 'failed',
                'message'      => ErrorMessageConstants::ERROR_MESSAGES[$this->defaultMessageLanugage]['somethingWrong'],
            ];

            $this->logger->error(Utils::json($params), $error);

            return $error;

        } catch (\Throwable $exception) {
            $error = [
                'code'         => 600,
                'status'       => 'failed',
                'message'      => ErrorMessageConstants::ERROR_MESSAGES[$this->defaultMessageLanugage]['somethingWrong'],
                'error_message' => $exception->getMessage()
            ];

            $this->logger->error(Utils::json($params), $error);

            return $error;
        }
    }

    /**
     * Get Client Ip from Server Headers
     */
    private function getIpAddress()
    {
        if (\PHP_SAPI !== 'cli' && \PHP_SAPI !== 'phpdbg') {
            $client  = $_SERVER['HTTP_CLIENT_IP'] ?? '';
            $forward = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '';
            $remote  = $_SERVER['REMOTE_ADDR'] ?? '';

            if (filter_var($client, \FILTER_VALIDATE_IP)) {
                return $client;
            }

            if (filter_var($forward, \FILTER_VALIDATE_IP)) {
                return $forward;
            }

            return $remote;
        }

        return null;
    }
}
