<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Storage;
use App\Http\Requests; 
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Encryption\DecryptException;
use Config,Mail,View,Redirect,Validator,Response; 
use Auth,Crypt,Hash,Lang,Input,Closure,URL; 
use JWTExceptionTokenInvalidException; 
use App\Helpers\Helper as Helper;
use App\User;
use Session; 
use App\Mail\SendGridMailAPI as SendGridMailAPI;
use App\Models\EmailOtpMapping;
use Illuminate\Support\Facades\Log;
use Illuminate\Log\Writer;
use Monolog\Logger;
use Illuminate\Contracts\Foundation\Application;  
use Monolog\Handler\StreamHandler;
use Jenssegers\Agent\Agent;
use App\Http\Controllers\BaseController;
use App\Http\Controllers\Utility\Utils;
use GuzzleHttp\Client;
use App\Models\Sql\Configuration;

class MemberController extends BaseController
{

    public function __construct(Request $request) {
       parent::__construct('Member'); 
    } 
    
   /* @method : register
    * @param : email,password,deviceID,firstName,lastName
    * Response : json
    * Return : token and user details
    * Author : kundan Roy
    * Calling Method : get  
    */
    public function registration(Request $request,User $user)
    {    
        //Server side valiation
        $request->merge([
                'email' => $request->reg_email,
                'password' =>  $request->reg_password,
                'name' => $request->reg_full_name,
            ]);
        
        $validator = Validator::make($request->all(), [
           'email' => 'required|email|unique:users',
           'password' => 'required'
        ]);
        /** Return Error Message **/
        if ($validator->fails()) {
                    $error_msg  =   [];
            foreach ( $validator->messages()->all() as $key => $value) {
                        array_push($error_msg, $value);     
                    }
            
            $response = array(
                "code"=>  401,
                "status"=>'failed',
                "message"=>$error_msg[0],
                'data'=>[]
            );  
            
            $params = $request->only('name','email');
            $this->logger->error(Utils::json($params), $response);
            
            return Response::json($response);    
        }  
       
        try {
            \DB::beginTransaction();

            $request->only('name','email', 'password');
            $password = Hash::make($request->get('password'));
            $request->merge(['user_code'=>'NA']);
            $request->merge(['super_store_id'=>Config::get('app.super_store_id')??null]);
            $request->merge(['store_id'=>null]);
            $request->merge(['password'=>$password??null]);
            $request->merge(['user_status_id'=>3]);
            $request->merge(['login_social_platform_id'=>7]);
          
            $data =  $request->only('name','email', 'password','user_code','super_store_id','password','user_status_id','login_social_platform_id');
            
            $user = User::create($data);

            $user = User::find($user->id);
            $user->user_code = Config::get('app.super_store_id').'-'.$user->id.'-'.time();
            $user->save();

            $request->merge(['user_code'=>$user->user_code]); 
            $table_cname = \Schema::getColumnListing('user_default_profile');
            $profile = [];
            foreach ($data as $key => $value) { 
               if(in_array($key, $table_cname)){
                    if($request->get($key)){
                        $profile[$key] = $value;
                    }
               }
            } 

            $user_profile = \DB::table('user_default_profile')->insert($profile);
            \DB::commit();
            $msg =  __('messages.seccessRegistration');
            $code = 200;
        } catch (\Exception $e) {
             \DB::rollback();
            $msg = $e->getMessage();
            $code = 601;
        }
         
        /** --send mail after registration-- **/
        
        $subject = __('messages.emailVerificationSubject');
        $data = [   
                    'from_email_address' => FROM_EMAIL_ADDRESS,
                    'from_name' => FROM_NAME,
                    'to_name'=>$request->get('name'),
                    'to_email'=>$request->get('email'),
                    'subject' => $subject,
                    'otp' =>  $this->generateOtp($request->get('email'))
                ];
               
        $template = "email_verification";
        $mail = new SendGridMailAPI((object)$data, $template);
        $mail->send();
       
        return response()->json(
                            [ 
                                "code"=>$code,
                                "status"=>'success',
                                "message"=>$msg,
                                'data'=>[]
                            ]
                        );
    }

  /**
    * description : generate otp
    * @param email 
    * return integer
    */ 
    public function generateOtp( $email) {
        
        $otp = mt_rand(1000, 9999); 
        $data['otp'] = $otp;
        $data['email'] = $email;
        $data['super_store_id'] = config('app.super_store_id');
        $data['otp_status'] = 'success';
        $data['otp_validity'] = date('Y-m-d');
        \DB::table('email_otp_mapping')->insert($data);
         
        return  $otp;
    }
    /* @method : get User Profile 
    * Response : json 
    * Author : kundan Roy
    * Calling Method : get  
    */
    public function getProfile(Request $request,User $user)
    {       
        $userData =Auth::user();
         
        return view('account-details',compact('userData'));
    }
/* @method : update User Profile
    * @param : email,password,deviceID,firstName,lastName
    * Response : json
    * Return : token and user details
    * Author : kundan Roy
    * Calling Method : get  
    */
    public function updateProfile(Request $request,User $user)
    {       
        
        $messages = [
            'email.exists' => __('messages.emailDoesntExist'),
            'email.email' => __('messages.invalidEmail')
        ];

        $validator = Validator::make($request->all(), [
           'email' => 'required|email|exists:email_otp_mapping,code,email',
           'full_name' => 'required' 
        ],$messages);
        /** Return Error Message **/
        if ($validator->fails()) {
                    $error_msg  =   [];
            foreach ( $validator->messages()->all() as $key => $value) {
                        array_push($error_msg, $value);     
                    }
                            
            return Response::json(array(
                'status' => 0,
                 'code' => 600,
                'message' => $error_msg[0],
                'data'  =>  ''
                )
            );
        }  
        User::where('email',$request->get('email'))->update(['name'=>$request->get('full_name')]);
        return  response()->json([ 
                    "status"=>1,
                    "code"=> 200,
                    "message"=> __('messages.profileUpdated'),
                    'data' => []
                    ]
                );   
    }

   /* @method : login
    * @param : email,password
    * Response : json
    * Return : user details
    * Author : kundan Roy   
    */
    public function login(Request $request)
    {    
         //Server side valiation
        $validator = Validator::make($request->all(), [
           'email' => 'required|email',
            'password' => 'required'
        ]);
        /** Return Error Message **/
        if ($validator->fails()) {
                    $error_msg  =   [];
            foreach ( $validator->messages()->all() as $key => $value) {
                        array_push($error_msg, $value);     
                    }
                            
            return Response::json(array(
                'status' => 0,
                 'code' => 600,
                'message' => $error_msg[0],
                'data'  =>  ''
                )
            );
        }  
       // dd($request->all()); exit();
        try {
             if (Auth::attempt(['email'=>$request->get('email'),'password'=>$request->get('password')])) {

                $status     = 1;
                $code       = 200;
                $message    = __('messages.loginSuccess');
            }else{
                $status     = 0;
                $code       = 201;
                $message    =  __('messages.loginFailed');
            }
            return response()->json([ "status"=>$status,"code"=>$code,"message"=>$message ,'data' => []]);
        }catch( Exception $ex ){
            return response()->json([ "status"=>0,"code"=>500,"message"=>$ex->getMessage() ,'data' => []]);
        }
    }

   /* @method : logout 
    * Response : "logout message"
    * Return : json response 
    */
    public function logout(Request $request) {
        Auth::logout();
        return redirect('/');
    }
 
   /* @method : Email Verification
    * @param : token_id
    * Response : json
    * Return : json
   */
    public function emailVerification(Request $request)
    {
        $verification_code = $request->input('verify_reg_email_otp');
        $email    = $request->input('email');
        $messages = [
            'otp.exists' => __('messages.invalidOTP'),
            'email.exists' => __('messages.invalidOTP'),
        ];
       
        $validator = Validator::make($request->all(), [
           'otp' => 'required|exists:email_otp_mapping,otp', 
           'email' => 'exists:email_otp_mapping,email',
        ],$messages); 
        /** Return Error Message **/
        if ($validator->fails()) {
                    $error_msg  =   [];
            foreach ( $validator->messages()->all() as $key => $value) {
                        array_push($error_msg, $value);     
                    }
                            
            return Response::json(array(
                'status' => 0,
                 'code' => 600,
                'message' => $error_msg[0],
                'data'  =>  []
                )
            );
        }  
 
        return  response()->json([ 
                    "status"=>1,
                    "code"=> 200,
                    "message"=> __('messages.emailVerificationStatusSuccess'),
                    'data' => []
                    ]
                );   
    }
   
   /* @method : forget password
    * @param : token,email
    * Response : json
    * Return : json response 
    */
    public function forgetPassword(Request $request)
    {  
        $messages = [
            'email.required' => __('messages.missingEmail'),
            'email.email' => __('messages.invalidEmail'),
            'email.exists' => __('messages.emailMismatch'),
        ];

        $validator = Validator::make($request->all(), [
           'email' => 'required|email|exists:users,email', 
        ],$messages);
        /** Return Error Message **/
        if ($validator->fails()) {
                    $error_msg  =   [];
            foreach ( $validator->messages()->all() as $key => $value) {
                        array_push($error_msg, $value);     
                    }
                            
            return Response::json(array(
                'status' => 0,
                'code' => 600,
                'message' => $error_msg[0],
                'data'  =>  ''
                )
            );
        }  
        $user =  User::where('email',$request->get('email'))->first();
        $otp  =   Helper::forgetPasswordEmail($user);
       
        return   response()->json(
                    [ 
                        "status"=>1,
                        "code"=> 200,
                        "message"=> __('messages.resetOtpLink'),
                        'data' => ['email'=>$request->get('email'),'otp'=>$otp]
                    ]
                );
    }

    
    /** @method :changePassword
    * @param : Request
    * Response : json
    * Request : json  
    */
    public function changePassword(Request $request)
    {       
        
        $validator = Validator::make($request->all(), [
           'old_password' => 'required',
           'change_password' => 'required|min:6',
           'change_confirm_password' => 'required|same:change_password',
 
        ]);
        /** Return Error Message **/
        if ($validator->fails()) {
                    $error_msg  =   [];
            foreach ( $validator->messages()->all() as $key => $value) {
                        array_push($error_msg, $value);     
                    }
                            
            return Response::json(array(
                'status' => 0,
                'message' => $error_msg[0],
                'data'  =>  ''
                )
            );
        }  
        $password = \Hash::make($request->get('change_confirm_password')); 
        User::where('id',Auth::user()->id)->update(['password'=>$password]);
        return  response()->json([ 
                    "status"=>1,
                    "code"=> 200,
                    "message"=> __('messages.passChangeSuccess'),
                    'data' => []
                    ]
                );  
    }
  /** @method :setNewPassword
    * @param : Request
    * Response : json
    * Request : json  
    */
    public function setNewPassword(Request $request)
    {       
        $verification_code = $request->input('otp');
        $email    = $request->input('email');

        $messages = [
            'otp.exists' => __('messages.invalidOTP'),
            'email.exists' => __('messages.invalidOTP'),
            'newPassword' => __('messages.missingNewPass'),
            'confirmPassword.same' => __('messages.confirmPassword'),
        ];

        $validator = Validator::make($request->all(), [
           'otp' => 'required|exists:email_otp_mapping,otp', 
           'email' => 'exists:email_otp_mapping,email', 
           'newPassword' => 'required|min:6',
           'confirmPassword' => 'required|same:newPassword',
 
        ],$messages);
        /** Return Error Message **/
        if ($validator->fails()) {
                    $error_msg  =   [];
            foreach ( $validator->messages()->all() as $key => $value) {
                        array_push($error_msg, $value);     
                    }
                            
            return Response::json(array(
                'status' => 0,
                'message' => $error_msg[0],
                'data'  =>  ''
                )
            );
        }  
        $password = \Hash::make($request->get('newPassword')); 
        User::where('email',$request->get('email'))->update(['password'=>$password]);
        return  response()->json([ 
                    "status"=>1,
                    "code"=> 200,
                    "message"=> __('messages.passChangeSuccess'),
                    'data' => []
                    ]
                );  
    }


    public function addDevice(Request $request){

    }

    public function getDevice(Request $request){
        
    }
    public function detectDevice(Request $request){
        $agent = new Agent();
         
        $browser = $agent->browser();
        $platform = $agent->platform();
        $default_id = \Illuminate\Support\Str::random(32);

    }         

}
