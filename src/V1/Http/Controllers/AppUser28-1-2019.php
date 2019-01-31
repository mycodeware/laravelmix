<?php

namespace Jet\Publicam\JetEngage\V1\Http\Controllers;

use Validator;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Models\Sql\Configuration;
use App\Models\EmailOtpMapping;
use App\Models\UserDefaultProfile;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Redis;
use Jet\Publicam\JetEngage\V1\Http\Controllers\Utility\Utils;
use Jet\Publicam\JetEngage\V1\Http\Controllers\BaseController;
use Jet\Publicam\JetEngage\V1\Models\Sql\DeviceDetail;
use App\User;
use Illuminate\Support\Facades\Hash;
use Auth;
use App\Mail\SendGridMailAPI;


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
class AppUser extends BaseController
{
    public $otp_validity = "" ;// t - 15 min

    public $otp_status = "sent"; //default

    protected $defaultMessageLanugage;

    public function __construct()
    {
        parent::__construct('appUser');
    }

   
    public function checkLogin(Request $request)
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
                "status" => Lang::get('v1::messages.failed'),
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
          \App::setLocale($language);
         $messages = [
            'required' => Lang::get('v1::messages.required')
        ];
        $rule = [
            'email' => 'required|email', 
            'password'=>'required|min:'.getenv('PASSWORD_MIN_LENGTH').'|max:'.getenv('PASSWORD_MAX_LENGTH'),
            'deviceId' => 'required',
            'deviceToken' => 'required',
           
        ];
        $validator = Validator::make($params, $rule, $messages);
        if ($validator->fails()) {
            $errors = $validator->errors();

            foreach ($errors->all() as $error) {
                $errorResponse = [
                    'code' => 601,
                    'status' => Lang::get('v1::messages.failed'),
                    'message' => $error
                ];
                $this->logger->error(Utils::json($params), $errorResponse);
                return $errorResponse;
            }
        }

        $email=$request->email;
        $pass=$request->password;
        $credentials = $request->only("email","password");

         if (Auth::attempt($credentials)) {

            $superStoreId=getenv('SuperStoreId');
            $userCode=Auth::user()->user_code;
            $deviceId=$request['deviceId'];               
            $deviceToken=$request['deviceToken'];
            $platform=$params['locale']['platform'];

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

 $responseData = [
                'user_code'       => Auth::user()->user_code,
                'super_store_id'  => Auth::user()->super_store_id
          
            ];
         // Authentication passed...
                $SuccessResponse = [
                    'code' => 200,
                    'status' =>  Lang::get('v1::messages.success'),
                    'message' =>Lang::get('v1::messages.loginSuccess'),
                    'data'=>$responseData
                ];
                $this->logger->error(Utils::json($params), $SuccessResponse);
                return json_encode($SuccessResponse);
           
        }
        else
        {

              $LoginFailResponse = [
                    'code' => 601,
                    'status' =>  Lang::get('v1::messages.failed'),
                    'message' => Lang::get('v1::messages.loginFailed')
                ];

                $this->logger->error(Utils::json($params), $LoginFailResponse);

                return json_encode($LoginFailResponse);
        }

     
    }

    function generateNumericOTP($n) 
        {     
            $generator = "1357902468"; 
            $result = ""; 
          
            for ($i = 1; $i <= $n; $i++) { 
                $result .= substr($generator, (rand()%(strlen($generator))), 1); 
            } 
          // Return result 
            return $result; 
        } 


     public function forgotPassword(Request $request){

          $payload = $request->all();
        /**
         * Payload Decryption
         */
        $decryptedPayload  = $this->decryptPayload($payload);

        $language = strtoupper($payload['locale']['language'] ?? '');

        if ($decryptedPayload['code'] !== 200) {
            $error = [
                "code" => 400,
                "status" => Lang::get('v1::messages.failed'),
                "message" =>  Lang::get('v1::messages.invalidPayload')
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


        $messages = [
            'required' => Lang::get('v1::messages.required')
        ];

        $rule = ['email' => 'required|email'];

        $validator = Validator::make($params, $rule, $messages);

        if ($validator->fails()) {
            $errors = $validator->errors();

            foreach ($errors->all() as $error) {
                $errorResponse = [
                    'code' => 601,
                    'status' => Lang::get('v1::messages.failed'),
                    'message' => $error
                ];

                $this->logger->error(Utils::json($params), $errorResponse);
                return $errorResponse;
            }
        }
// check if any account associated with email

$user = User::where('email', '=',$request['email'])->first();
      
     if($user === NULL)
     {

              $errorResponse = [
                    'code' => 601,
                    'status' => Lang::get('v1::messages.failed'),
                    'message' =>Lang::get('v1::messages.emailDoesntExist')
                ];

                
                return $errorResponse;
                exit();

     }
     else
     {
        //associated account found 

        $otp =$this->generateNumericOTP(getenv('OTP_MIN'));

        $request->request->add(['otp' => $otp ]); 
        $request->request->add(['otp_status' => $this->otp_status ]); 

        $request->request->add(['super_store_id' => getenv('SuperStoreId') ]);
        

        $date = date("Y-m-d H:i:s");
        $time = strtotime($date);
        $time = $time + (getenv('OTP_EXP') * 60);
        $validfor = date("Y-m-d H:i:s", $time);

        $request->request->add(['otp_validity' => $validfor ]); 

        EmailOtpMapping::create(request(['email','super_store_id','otp','otp_validity','otp_status']));

                    // send OTP Via email
                     $env_email= getenv('API_MAIL_FROM_ADDRESS');
                     $data = [ 'from_email_address' => $env_email,
                                        'from_name' => 'Channel Fight',
                                        'to_name'=>$user->name,
                                        'to_email'=>$user->email,
                                        'otp'=>$otp,
                                        'subject' =>'Channel Fight Forgot Password OTP' 
                                       
                                    ];
                    $template = "api_email_verification";
                    $mail = new \App\Mail\SendGridMailAPI((object)$data, $template);
                    // send OTP Via email
                     $mail->send();
                   
                     $forgotsuccess = [
                            'code' => 200,
                            'status' => Lang::get('v1::messages.success'),
                            'message' => Lang::get('v1::messages.emailSendSuccess')
                                    ];

                return json_encode($forgotsuccess);
     }

    }




        // appResetPassword
        public function appResetPassword(Request $request)
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
                "status" =>Lang::get('v1::messages.failed'),
                "message" =>Lang::get('v1::messages.invalidPayload')
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


         $messages = [
            'required' => 'Request cannot be handled due to missing :attribute value',
        ];

        $rule = [
            
            'email' => 'required|email',
            'otp' =>'required|max:'.getenv('OTP_MAX').'|min:'.getenv('OTP_MIN'),
            
            'password'=>'required|min:'.getenv('PASSWORD_MIN_LENGTH').'|max:'.getenv('PASSWORD_MAX_LENGTH').'|confirmed',
            'password_confirmation'=>'required'
            
        ];

        $validator = Validator::make($params, $rule, $messages);

        if ($validator->fails()) {
            $errors = $validator->errors();

            foreach ($errors->all() as $error) {
                $errorResponse = [
                    'code' => 601,
                    'status' => Lang::get('v1::messages.failed'),
                    'message' => $error
                ];

                 $this->logger->error(Utils::json($payload), $errorResponse);
                return $errorResponse;
            }
        }
        

        

        $flag = EmailOtpMapping::where('email', '=',$request['email'])
                                    ->where('otp','=',$request['otp'])
                                    ->where('otp_validity','>',date("Y-m-d H:i:s"))->first();

       if($flag === NULL)
       {

             $errorResponse = [
                'code' => 601,
                'status' => Lang::get('v1::messages.failed'),
                'message' =>Lang::get('v1::messages.invalidOTP')
                
            ];
$this->logger->error(Utils::json($payload), $errorResponse);
            return $errorResponse;
            exit();
        }else{

                $pass = Hash::make($request['password']);

                $userinfo=User::where('email',$request['email'])->update(['password'=>$pass]);


                if($userinfo === NULL)
                {
                    echo "Something went wrong";

                    $errorResponse = [
                    'code' => 601,
                    'status' => Lang::get('v1::messages.failed'),
                    'message' => Lang::get('v1::messages.somethingWrong')
                ];
                    $this->logger->error(Utils::json($payload), $errorResponse);
                    return $errorResponse;
                }

                $errorResponse = ['code' => 200,
                                  'status' =>  Lang::get('v1::messages.success'),
                                  'message' => Lang::get('v1::messages.passChangeSuccess') 
                                 ];
                            $this->logger->error(Utils::json($payload), $errorResponse);
                             return $errorResponse;

            }

        }

        public function socialLogin(Request $request)
        {   $payload = $request->all();
        /**
         * Payload Decryption
         */
        $decryptedPayload  = $this->decryptPayload($payload);
        $language = strtoupper($payload['locale']['language'] ?? '');

        if ($decryptedPayload['code'] !== 200) {
            $error = [
                "code" => 400,
                "status" => Lang::get('v1::messages.failed'),
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
          \App::setLocale($language);
         $messages = [
            'required' => Lang::get('v1::messages.required')
        ];
        $rule = [
            'username' =>'required',
            'email' => 'required|email', 
            'social_platfrom_id'=>'required',
            'socialToken'=>'required',
            'socialAccountId'=>'required',
            'device_id'=>'required'
           
        ];
        $validator = Validator::make($params, $rule, $messages);
        if ($validator->fails()) {
            $errors = $validator->errors();

            foreach ($errors->all() as $error) {
                $errorResponse = [
                    'code' => 601,
                    'status' => Lang::get('v1::messages.failed'),
                    'message' => $error
                ];
                $this->logger->error(Utils::json($params), $errorResponse);
                return $errorResponse;
            }
        }

                        

       
                           // check if any account associated with email

                        $user = User::where('email', '=',$request['email'])->where('login_social_platform_id','=',$request['social_platfrom_id'])->where('social_media_user_id','=',$request['socialAccountId'])->first();
                          
                        
                         if($user === NULL)
                         {

                                  $errorResponse = [
                                        'code' => 601,
                                        'status' => Lang::get('v1::messages.failed'),
                                        'message' =>Lang::get('v1::messages.emailDoesntExist')
                                    ];

                                    
                                    return $errorResponse;
                                    exit();

                         }
                         else
                         {
                           


                        $otp =$this->generateNumericOTP(getenv('OTP_MIN'));

                        $request->request->add(['otp' => $otp ]); 
                        $request->request->add(['otp_status' => $this->otp_status ]); 

                        $request->request->add(['super_store_id' => getenv('SuperStoreId') ]);
                        

                        $date = date("Y-m-d H:i:s");
                        $time = strtotime($date);
                        $time = $time + (getenv('OTP_EXP') * 60);
                        $validfor = date("Y-m-d H:i:s", $time);

                        $request->request->add(['otp_validity' => $validfor ]); 

        EmailOtpMapping::create(request(['email','super_store_id','otp','otp_validity','otp_status']));

                                    // send OTP Via email
                                     $env_email= getenv('API_MAIL_FROM_ADDRESS');
                                     $data = [ 'from_email_address' => $env_email,
                                                        'from_name' => 'Channel Fight',
                                                        'to_name'=>$user->name,
                                                        'to_email'=>$user->email,
                                                        'otp'=>$otp,
                                                        'subject' =>'Channel Fight Forgot Password OTP' 
                                       
                                    ];
                                            $template = "api_email_verification";
                                            $mail = new \App\Mail\SendGridMailAPI((object)$data, $template);
                                           
                                             $mail->send();
                                        // send OTP Via email

                                         $forgotsuccess = [
                                                'code' => 200,
                                                'status' => Lang::get('v1::messages.success'),
                                                'message' => Lang::get('v1::messages.emailSendSuccess')
                                                        ];

                            return json_encode($forgotsuccess);
                         }
          }

          public function socialUser(Request $request)
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
                "status" =>Lang::get('v1::messages.failed'),
                "message" =>Lang::get('v1::messages.invalidPayload')
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


         $messages = [
            'required' => 'Request cannot be handled due to missing :attribute value',
        ];

                          $rule = [
            
                                'email' => 'required|email',
                                'otp' =>'required|max:'.getenv('OTP_MAX').'|min:'.getenv('OTP_MIN'),
                                'deviceId'=>'required'
                            ];

                             $validator = Validator::make($params, $rule, $messages);

                                                if ($validator->fails()) {
                                                    $errors = $validator->errors();

                                                    foreach ($errors->all() as $error) {
                                                        $errorResponse = [
                                                            'code' => 601,
                                                            'status' => Lang::get('v1::messages.failed'),
                                                            'message' => $error
                                                        ];

                                                         $this->logger->error(Utils::json($payload), $errorResponse);
                                                        return $errorResponse;
                                                    }
                                                }
                                                

                                                   $flag = EmailOtpMapping::where('email', '=',$request['email'])
                                    ->where('otp','=',$request['otp'])
                                    ->where('otp_validity','>',date("Y-m-d H:i:s"))->first();

                                           if($flag === NULL)
                                           {

                                                 $errorResponse = [
                                                    'code' => 601,
                                                    'status' => Lang::get('v1::messages.failed'),
                                                    'message' =>Lang::get('v1::messages.invalidOTP')
                                                    
                                                ];
                                    $this->logger->error(Utils::json($payload), $errorResponse);
                                                return $errorResponse;
                                                exit();
                                            }
                                            else
                                            {
                                                $user = User::where('email','=',$request['email'])->first();
                                               
                                                //$loginuser=Auth::loginUsingId($user->id);

                                               $loginuser = Auth::login($user);

                                                if(Auth::user()->id)
                                                {
                                                     $data = [
                                                            'user_code'       => Auth::user()->user_code,
                                                            'super_store_id'  => Auth::user()->super_store_id
                                                      
                                                        ];

                                                     $SuccessResponse = [
                                                            'code' => 200,
                                                            'status' =>  Lang::get('v1::messages.success'),
                                                            'message' =>Lang::get('v1::messages.loginSuccess'),
                                                            'data'=>$data
                                                        ];
                                                        $this->logger->error(Utils::json($params), $SuccessResponse);
                                                        return json_encode($SuccessResponse);
                                                }

                                            }

          }




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

}
