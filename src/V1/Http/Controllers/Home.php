<?php

namespace Jet\Publicam\JetEngage\V1\Http\Controllers;

use Validator;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Lang;
use Jet\Publicam\JetEngage\V1\Models\Sql\User;
use Jet\Publicam\JetEngage\V1\Http\Controllers\Utility\Utils;
use Jet\Publicam\JetEngage\V1\Http\Controllers\BaseController;
use Jet\Publicam\JetEngage\V1\Http\Controllers\Utility\Validation;

class Home extends BaseController
{
    public function __construct()
    {
        parent::__construct('home');
    }

    /**
     * Custom (Email) Login
     * @return Response
     */
    public function sendConsent(Request $request)
    {
        $payload = $request->all();

        /**
         * Payload Decryption
         */
        $decryptedPayload  = $this->decryptPayload($payload);

        $language = strtoupper($payload['locale']['language'] ?? '');

        if ($decryptedPayload['code'] !== 200) {
            $error = [
                "code" => 400,
                "status" => "Failed",
                "message" => Lang::get('v1::messages.invalidPayload')
            ];

            $this->logger->error(Utils::json($payload), $error);
            return $error;
        }

        $params = $decryptedPayload['data'];

        //Locale Validation
        $checkLocale = Utils::validateLocale($params);

        if ($checkLocale['code'] !== 200) {
            $this->logger->error(Utils::json($params), $checkLocale);
            return $checkLocale;
        }

        $language = strtolower($params['locale']['language']) ?? '';
        $platform = $params['locale']['platform'];

        \App::setLocale($language);

        $messages = [
            'required' => Lang::get('v1::messages.required'),
        ];

        $rule = [
            'superStoreId' => 'required',
            'userCode' => 'required',
        ];

        $validator = Validator::make($params, $rule, $messages);

        if ($validator->fails()) {
            $errors = $validator->errors();

            foreach ($errors->all() as $error) {
                $errorResponse = [
                    'code' => 601,
                    'status' => 'failed',
                    'message' => $error
                ];

                $this->logger->error(Utils::json($params), $errorResponse);

                return $errorResponse;
            }
        }

        $superStoreId = $params['superStoreId'];
        $userCode = $params['userCode'];

        /**
         * Check whether provided user code exists or not
         */
        if (Validation::userExists($superStoreId + 0, $userCode) === false) {
            $error = [
                "code" => 601,
                "status" => "failed",
                "message" => Lang::get('v1::messages.invalidUserCode'),
            ];
            $this->logger->error(Utils::json($params), $error);
            return $error;
        }

        try {

            $getUser = User::where([
                'super_store_id' => $superStoreId,
                'user_code' => $userCode
            ])->first();

            if($getUser !== null) {
                if(!isset($getUser->user_consent) || $getUser->user_consent == '') {
                    $getUser->user_consent = 1;
                    $getUser->consent_accepted_at = Carbon::now()->toDateTimeString();
                    $getUser->save();
                }
            }

            $response = [
                'code' => 200,
                'message' => 'success',
                'status' => Lang::get('v1::messages.success'),
            ];
            $this->logger->info(Utils::json($params), $response);

            return $response;

        } catch (\Throwable $exception) {

            $errorResponse = [
                'code' => 400,
                'status' => 'failed',
                'message' => Lang::get('v1::messages.somethingWrong'),
                'error_message' => $exception->getMessage()
            ];

            $this->logger->error(Utils::json($params), $errorResponse);

            return $errorResponse;
        }
    }

}
