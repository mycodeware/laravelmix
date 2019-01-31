<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use Auth;
use Socialite;
use Response;
use InvalidStateException;
use ClientException;

class AuthController extends Controller
{
   

    public function redirectToProvider($provider)
    {   
        return Socialite::driver($provider)->redirect();
    }

    /**
     * Obtain the user information from provider.  Check if the user already exists in our
     * database by looking up their provider_id in the database.
     * If the user exists, log them in. Otherwise, create a new user then log them in. After that 
     * redirect them to the authenticated users homepage.
     * social login
     * @return Response
     */
    public function handleProviderCallback($provider='google')
    {
        try{
            $user = Socialite::driver($provider)->stateless()->user();
            $socialUser = null;  
          
            //Check is this email present
            $checkUser = User::where($provider.'_id', '=', $user->id)->first(); 
            $data = [];
            if ($checkUser) {
                Auth::login($checkUser);
            }else{

                if($provider=='facebook'){

                    $data['password'] = bcrypt(str_random(16));
                    $name               = explode(" ", $user->name);
                    $data['first_name'] = isset($name[0])?$name[0]:'';
                    $data['last_name']  = isset($name[1])?$name[1]:'';
                    //$data['name'] = isset($user->name)?$user->name:'';
                    
                    $data['email']      = isset($user->email)?$user->email:'missing' . str_random(10).'@bankersolution.com';
                    $data['profile_image'] = isset($user->avatar)?$user->avatar:null;
                    $data[$provider.'_id'] = isset($user->id)?$user->id:'NA';
                    $data['status'] = 1;

                }else{

                    $data['password'] = bcrypt(str_random(16));
                    $name               = explode(" ", $user->name);
                    $data['first_name'] = isset($name[0])?$name[0]:'';
                    $data['last_name']  = isset($name[1])?$name[1]:'';
                    //$data['name'] = isset($user->name)?$user->name:'';
                    
                    $data['email']      = isset($user->email)?$user->email:'missing' . str_random(10).'@bankersolution.com';
                    $data['profile_image'] = isset($user->avatar)?$user->avatar:null;
                    $data[$provider.'_id'] = isset($user->id)?$user->id:'NA';
                    $data['gender'] = isset($user->user['gender'])?$user->user['gender']:'';
                    $data['occupation'] =  isset($user->user['occupation'])?$user->user['occupation']:'';
                    $data['about_me'] = isset($user->user['aboutMe'])?$user->user['aboutMe']:'';
                    $data['status'] = 1;

                }
                $user_data = \DB::table('users')->insert($data);
                $checkUser = User::where($provider.'_id', '=', $user->id)->first(); 
                Auth::login($checkUser);
            }
            return redirect('myaccount');

        }catch(\GuzzleHttp\Exception\ClientException $e){
            return redirect('/');
        }   
    }
}
