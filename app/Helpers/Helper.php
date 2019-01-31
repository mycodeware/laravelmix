<?php

declare(strict_types=1);

namespace App\Helpers;

use Illuminate\Http\Request;
use App\User;
use Hash;
use Input;
use Mail;
use View; 
use App\Mail\SendGridMailAPI as SendGridMailAPI;

class Helper
{ 
  
  /**
    * sender email id
    * @var from_email_address
    */
    public static $from_email_address;
  /**
    * sender name
    * @var from_name
    */
    public static $from_name;

  /**
    * sender email id and seder name initialise
    * @var from_email_address and from_name
    * return : void
    * @param = null
    */

    public function __construct() 
    {
        self::$from_email_address = 'kundan.roy@jetsynthesys.com';
        self::$from_name = 'Channelfight' ;

    }

  /**
    * function used to check valid phone number
    *
    * @param = null
    */
    public static function FormatPhoneNumber($number)
    {
        return preg_replace('~.*(\d{3})[^\d]{0,7}(\d{3})[^\d]{0,7}(\d{4}).*~', '($1) $2-$3', $number) . "\n";
    } 

  /**
    * function used to generate generateOtp
    *
    * @param = email
    * return integer
    */
    public function generateOtp( $email) : int{
        
        $otp = mt_rand(1000, 9999);

        $data['otp'] = $otp;
        $data['email'] = $email;
        $data['super_store_id'] = config('app.super_store_id');
        $data['otp_status'] = 'success';
        $data['otp_validity'] = date('Y-m-d');
        \DB::table('email_otp_mapping')->insert($data);
         
         return  $otp;
    }
     /**
    * function used to send email otp to reset password
    *
    * @param  emailContent
    */
    public static function forgetPasswordEmail($emailContent)
    { 
        $otp = (new Helper())->generateOtp($emailContent->email);
       
        $subject = "Reset account password otp!";
        $data = [   
                    'from_email_address' =>  self::$from_email_address,
                    'from_name' => self::$from_name,
                    'to_name'   =>  $emailContent->name,
                    'to_email'  =>  $emailContent->email,
                    'subject'   =>  $subject,
                    'otp'       => $otp
                ];
               
        $template = "forgot_password_otp";

        $mail = new SendGridMailAPI((object)$data, $template);
        $mail->send();
        return $otp;
    }
  /**
    * function used to send email after registration
    *
    * @param  emailContent
    */

    public static function registrationEmail($request)
    {
        $subject = "Welcome to Channelfight! Verify your email address to get started";
        $data = [   
                    'from_email_address' =>  self::$from_email_address,
                    'from_name' => self::$from_name,
                    'to_name'   =>  $request->get('name'),
                    'to_email'  =>  $request->get('email'),
                    'subject'   =>  $subject,
                    'otp'       =>  $this->generateOtp($request->get('email'))
                ];
               
        $template = "email_verification";
        $mail = new SendGridMailAPI((object)$data, $template);
        $mail->send();
    }

}
