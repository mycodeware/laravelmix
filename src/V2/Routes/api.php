<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
 
$version = 'v2'; 

Route::middleware('auth:api')->get('/'.$version, function (Request $request) use($version){
    return $request->user();
});

Route::group([
	    'prefix' => $version
	], function() use($version)
    {   
        Route::get('/welcome', function () use($version) { 
		    return ["welcome to api version ".$version];
		}); 
        // jwt-auth middleware

        Route::group(['middleware' => ['checkHeader'.$version,'jwtAuth'.$version]], function () use($version)
        {
        	Route::match(['post','get'],'jwt',function(){
            	  return ['jwt test'];
            });
        });


		// if route not found
	    Route::any('{any}', function(){
				$data = [
							'status'	=>	0,
							'code'		=>	400,
							'message' 	=> 'Bad request'
						];
				return \Response::json($data);
		});
});