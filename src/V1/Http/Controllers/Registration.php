<?php

namespace Jet\Publicam\JetEngage\V1\Http\Controllers;

use Validator;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Mail\SendGridMailAPI;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Redis;
use Jet\Publicam\JetEngage\V1\Models\Sql\User;
use Jet\Publicam\JetEngage\V1\Models\Sql\UserStatus;
use Jet\Publicam\JetEngage\V1\Models\Sql\DeviceDetail;
use Jet\Publicam\JetEngage\V1\Models\Sql\PlatformToken;
use Jet\Publicam\JetEngage\V1\Models\Sql\EmailOtpMapping;
use Jet\Publicam\JetEngage\V1\Models\Sql\UserDefaultProfile;
use Jet\Publicam\JetEngage\V1\Http\Controllers\Utility\Utils;
use Jet\Publicam\JetEngage\V1\Models\Sql\LoginSocialPlatform;
use Jet\Publicam\JetEngage\V1\Http\Controllers\BaseController;
use Jet\Publicam\JetEngage\V1\Http\Controllers\Utility\Validation;

class Registration extends BaseController
{
    public function __construct()
    {
        parent::__construct('registration');
    }

    /**
     * Custom (Email) Login
     * @return Response
     */
    public function custom(Request $request)
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
            'same' => Lang::get('v1::messages.same'),
            'userEmail.email' => Lang::get('v1::messages.invalidEmail'),
            'confirmEmail.email' => Lang::get('v1::messages.invalidConfirmEmail'),
            'password.min' => Lang::get('v1::messages.passwordMinLength'),
            'password.max' => Lang::get('v1::messages.passwordMaxLength')
        ];

        $rule = [
            'superStoreId' => 'required',
            'userName' => 'required',
            'userEmail' => 'required|email',
            'confirmEmail' => 'required|email|same:userEmail',
            'password' => 'required|min:'.env('PASSWORD_MIN_LENGTH').'|max:'.env('PASSWORD_MAX_LENGTH'),
            'deviceId' => 'required',
            'deviceToken' => 'required',
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

        $userName = $params['userName'];
        $userEmail = $params['userEmail'];
        $userPassword = $params['password'];
        $superStoreId = $params['superStoreId'];
        $hashPassword = Hash::make($userPassword);
        $deviceSerial = $params['deviceId'];
        $deviceToken = $params['deviceToken'];

        /**
         * Check email already exist
         */
        $emailExist = User::select('email')
            ->where('email', $userEmail)
            ->first();

        if($emailExist !== null) {
            $error = [
                'code' => 400,
                'status' => 'Failed',
                'message' => Lang::get('v1::messages.emailAlreadyExist')
            ];
            $this->logger->error(Utils::json($params), $error);
            return $error;
        }

        /**
         * Get pending user status id
         */
        $userStatus = UserStatus::select('id')
            ->where('user_status', 'Pending')
            ->first();
        $userStatusId = $userStatus->id;

        /**
         * Get Login social platform id for 'password'
         */
        $LoginSocialPlatform = LoginSocialPlatform::select('id')
            ->where('name', 'Password')
            ->first();
        $loginSocialPlatformId = $LoginSocialPlatform->id;

        try {
            DB::beginTransaction();

            /**
             * Create new user
             */
            $newUserData = new User;

            $newUserData->super_store_id = $superStoreId;
            $newUserData->user_status_id = $userStatusId;
            $newUserData->login_social_platform_id = $loginSocialPlatformId;
            $newUserData->password = $hashPassword;
            $newUserData->name = $userName;
            $newUserData->email = $userEmail;
            $newUserData->dob = '';
            $newUserData->gender = '';
            $newUserData->profile_pic_url = '';

            $newUserData->save();

            /**
             * Update user_code in new inserted user
             */
            $userId = $newUserData->id;

            $userCode = Utils::getUserCode($userId, $superStoreId);

            $newUserData->user_code = $userCode;
            $newUserData->save();

            /**
             * Create User Default Profile
             */
            $userDefaultProfile = new UserDefaultProfile;

            $userDefaultProfile->user_code = $userCode;
            $userDefaultProfile->user_status_id = $userStatusId;
            $userDefaultProfile->super_store_id = $superStoreId;
            $userDefaultProfile->name = $userName;
            $userDefaultProfile->email = $userEmail;
            $userDefaultProfile->dob = '';
            $userDefaultProfile->gender = '';
            $userDefaultProfile->profile_pic_url = '';

            $userDefaultProfile->save();

            $otp = Utils::generateOtp();

            $otpValidity = Carbon::now()->addHours('24')->toDateTimeString();

            $addEmailOtp = new EmailOtpMapping;
            $addEmailOtp->email = $userEmail;
            $addEmailOtp->otp = $otp;
            $addEmailOtp->super_store_id = $superStoreId;
            $addEmailOtp->otp_status = null;
            $addEmailOtp->otp_validity = $otpValidity;

            $addEmailOtp->save();

            $mailData = [
                'from_email_address' => 'imran.khan@jetsynthesys.com',
                'from_name' => 'imran.khan@jetsynthesys.com',
                'to_email' => $userEmail,
                'to_name' => $userName,
                'subject' => config('v1.mailerSubjects.userRegistration'),
                'otp' => $otp
            ];

            $mailTemplate = 'new_registration_otp';

            $mailer = new SendGridMailAPI((object) $mailData, $mailTemplate);

            if($mailer->send()) {

                $this->deviceToken(
                    $superStoreId,
                    $userCode,
                    $deviceSerial,
                    $deviceToken,
                    $platform
                );

                DB::commit();

                $response = [
                    'code' => 200,
                    'message' => 'success',
                    'status' => Lang::get('v1::messages.success'),
                ];
                $this->logger->info(Utils::json($params), $response);

                return $response;
            }

            DB::rollBack();

            $errorResponse = [
                'code' => 400,
                'status' => 'failed',
                'message' => Lang::get('v1::messages.somethingWrong'),
                'error_message' => "Mailer failed"
            ];

            $this->logger->error(Utils::json($params), $errorResponse);

            return $errorResponse;

        } catch (\Throwable $exception) {
            DB::rollBack();

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

    /**
     * Social (FB) Login
     * @return Response
     */
    public function social(Request $request)
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
            'same' => Lang::get('v1::messages.same'),
            'userEmail.email' => Lang::get('v1::messages.invalidEmail'),
        ];

        $rule = [
            'superStoreId' => 'required',
            'userName' => 'required',
            'userEmail' => 'required|email',
            'deviceId' => 'required',
            'deviceToken' => 'required',
            'socialAccountId' => 'required',
            'socialToken' => 'required',
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

        $userName = $params['userName'];
        $userEmail = $params['userEmail'];
        $deviceSerial = $params['deviceId'];
        $deviceToken = $params['deviceToken'];
        $socialAccountId = $params['socialAccountId'];
        $socialToken = $params['socialToken'];
        $profilePicture = $params['profilePicture'] ?? '';
        $superStoreId = $params['superStoreId'];
        /**
         * Check email already exist
         */
        $emailExist = User::select('email')
            ->where('email', $userEmail)
            ->first();

        if($emailExist !== null) {
            $error = [
                'code' => 400,
                'status' => 'Failed',
                'message' => Lang::get('v1::messages.emailAlreadyExist')
            ];
            $this->logger->error(Utils::json($params), $error);
            return $error;
        }

        /**
         * Get pending user status id
         */
        $userStatus = UserStatus::select('id')
            ->where('user_status', 'Pending')
            ->first();

        $userStatusId = $userStatus->id;

        /**
         * Get Login social platform id for 'password'
         */
        $LoginSocialPlatform = LoginSocialPlatform::select('id')
            ->where('name', 'Facebook')
            ->first();

        $loginSocialPlatformId = $LoginSocialPlatform->id;

        /**
         * Check Social Account already exist
         */
        $accountExist = User::select('email')
            ->where([
                'social_media_user_id' => $socialAccountId,
                'login_social_platform_id' => $loginSocialPlatformId
            ])
            ->first();

        if($accountExist !== null) {
            $error = [
                'code' => 400,
                'status' => 'Failed',
                'message' => Lang::get('v1::messages.emailAlreadyExist')
            ];
            $this->logger->error(Utils::json($params), $error);
            return $error;
        }

        try {
            DB::beginTransaction();

            /**
             * Create new user
             */
            $newUserData = new User;

            $newUserData->super_store_id = $superStoreId;
            $newUserData->user_status_id = $userStatusId;
            $newUserData->login_social_platform_id = $loginSocialPlatformId;
            $newUserData->name = $userName;
            $newUserData->email = $userEmail;
            $newUserData->password = null;
            $newUserData->dob = '';
            $newUserData->gender = '';
            $newUserData->social_media_user_id = $socialAccountId;
            $newUserData->profile_pic_url = $profilePicture;

            $newUserData->save();

            /**
             * Update user_code in new inserted user
             */
            $userId = $newUserData->id;

            $userCode = Utils::getUserCode($userId, $superStoreId);

            $newUserData->user_code = $userCode;
            $newUserData->save();

            /**
             * Create User Default Profile
             */
            $userDefaultProfile = new UserDefaultProfile;

            $userDefaultProfile->user_code = $userCode;
            $userDefaultProfile->user_status_id = $userStatusId;
            $userDefaultProfile->super_store_id = $superStoreId;
            $userDefaultProfile->name = $userName;
            $userDefaultProfile->email = $userEmail;
            $userDefaultProfile->dob = '';
            $userDefaultProfile->gender = '';
            $userDefaultProfile->profile_pic_url = $profilePicture;

            $userDefaultProfile->save();

            $otp = Utils::generateOtp();

            $otpValidity = Carbon::now()->addHours('24')->toDateTimeString();

            $addEmailOtp = new EmailOtpMapping;
            $addEmailOtp->email = $userEmail;
            $addEmailOtp->otp = $otp;
            $addEmailOtp->super_store_id = $superStoreId;
            $addEmailOtp->otp_status = null;
            $addEmailOtp->otp_validity = $otpValidity;

            $addEmailOtp->save();

            $mailData = [
                'from_email_address' => 'imran.khan@jetsynthesys.com',
                'from_name' => 'imran.khan@jetsynthesys.com',
                'to_email' => $userEmail,
                'to_name' => $userName,
                'subject' => config('v1.mailerSubjects.userRegistration'),
                'otp' => $otp
            ];

            $mailTemplate = 'new_registration_otp';

            $mailer = new SendGridMailAPI((object) $mailData, $mailTemplate);

            if($mailer->send()) {

                $this->deviceToken(
                    $superStoreId,
                    $userCode,
                    $deviceSerial,
                    $deviceToken,
                    $platform
                );

                $tokenResponse = $this->socialPlatformToken(
                    $superStoreId,
                    $userId,
                    $loginSocialPlatformId,
                    $userCode,
                    $socialAccountId,
                    $socialToken
                );

                if(isset($tokenResponse['code'])) {
                    if($tokenResponse['code'] !== 200) {
                        $this->logger->error(Utils::json($params), $tokenResponse);
                        return $tokenResponse;
                    }
                } else {
                    DB::rollBack();

                    $errorResponse = [
                        'code' => 400,
                        'status' => 'failed',
                        'message' => Lang::get('v1::messages.somethingWrong'),
                    ];
                    $this->logger->error(Utils::json($params), $errorResponse);
                    return $errorResponse;
                }

                DB::commit();

                $response = [
                    'code' => 200,
                    'message' => 'success',
                    'status' => Lang::get('v1::messages.success'),
                ];
                $this->logger->info(Utils::json($params), $response);

                return $response;
            }

            DB::rollBack();

            $errorResponse = [
                'code' => 400,
                'status' => 'failed',
                'message' => Lang::get('v1::messages.somethingWrong'),
                'error_message' => "Mailer failed"
            ];

            $this->logger->error(Utils::json($params), $errorResponse);

            return $errorResponse;

        } catch (\Throwable $exception) {
            DB::rollBack();

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

    /**
     * Update/Insert Firebase Token in DB
     */
    protected function deviceToken(
        int $superStoreId,
        string $userCode,
        string $deviceSerial,
        string $deviceToken,
        string $platform
    ): bool
    {
        try {

            $checkDevideDetails = DeviceDetail::where([
                'super_store_id' => $superStoreId,
                'user_code' => $userCode,
                'device_serial' => $deviceSerial,
                'platform' => $platform
            ])->first();

            if ($checkDevideDetails !== null) {
                $checkDevideDetails->device_id = $deviceToken;
                $checkDevideDetails->save();

                return true;
            }

            $devideDetails = new DeviceDetail;

            $devideDetails->super_store_id = $superStoreId;
            $devideDetails->user_code = $userCode;
            $devideDetails->device_serial = $deviceSerial;
            $devideDetails->device_id = $deviceToken;
            $devideDetails->platform = $platform;

            $devideDetails->save();

            return true;

        } catch (\Throwable $exception) {

            $this->logger->error(Utils::json($userCode), [$exception->getMessage()]);
            return false;
        }
    }

    /**
     * Update Device Token (Firebase token)
     *
     * @return array
     */
    public function updateToken(Request $request)
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
            'deviceId' => 'required',
            'deviceToken' => 'required',
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
        $deviceToken = $params['deviceToken'];
        $deviceId = $params['deviceId'];

        /**
         * Validate user Code
         */
        if (Validation::userExists($superStoreId, $userCode) === false) {
            $error = [
                'code' => 604,
                'status' => 'Failed',
                'message' => Lang::get('v1::messages.UserDosentExists')
            ];

            $this->logger->error(Utils::json($params), $error);

            return $error;
        }

        try {

            $result = $this->deviceToken(
                $superStoreId,
                $userCode,
                $deviceId,
                $deviceToken,
                $platform
            );

            if ($result !== true) {

                $error = [
                    'code' => 400,
                    'status' => 'Failed',
                    'message' => Lang::get('v1::messages.somethingWrong')
                ];

                $this->logger->error(Utils::json($params), $error);

                return $error;
            }

            $response = [
                'code' => 200,
                'status' => 'success',
                'message' => Lang::get('v1::messages.success')
            ];

            $this->logger->info(Utils::json($params), $response);

            return $response;

        } catch (\Throwable $exception) {
            $error = [
                'code' => 400,
                'status' => 'failed',
                'message' => Lang::get('v1::messages.somethingWrong'),
                'errorMessage' => $exception->getMessage()
            ];
            $this->logger->error(Utils::json($params), $error);

            return $error;
        }
    }

    /**
     * Update/Insert Social Platform Token in DB
     */
    public function socialPlatformToken(
        int $superStoreId,
        int $userId,
        int $socialPlatformId,
        string $userCode,
        string $socialAccountId,
        string $socialToken,
        string $socialSecret = null
    ): array
    {
        try {
            $checkAccountExists = PlatformToken::where([
                'user_code' => $userCode,
                'social_media_user_id' => $socialAccountId,
                'login_social_platform_id' => $socialPlatformId
            ])->first();

            if($checkAccountExists !== null) {

                $checkAccountExists->token = $socialToken;
                $checkAccountExists->token_secret = $socialSecret;

                $checkAccountExists->save();

            } else {
                $insertToken = new PlatformToken;

                $insertToken->user_id = $userId;
                $insertToken->user_code = $userCode;
                $insertToken->social_media_user_id = $socialAccountId;
                $insertToken->login_social_platform_id = $socialPlatformId;
                $insertToken->token = $socialToken;
                $insertToken->token_secret = $socialSecret;

                $insertToken->save();
            }

            return [
                'code' => 200,
                'status' => 'success',
                'message' => 'success'
            ];

        } catch (\Throwable $exception) {

            return [
                'code' => 400,
                'status' => 'failed',
                'message' => Lang::get('v1::messages.somethingWrong'),
                'error_message' => $exception->getMessage()
            ];
        }
    }

}
