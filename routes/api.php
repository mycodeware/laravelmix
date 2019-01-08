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

Route::group([
	    'prefix' => 'v1'
	], function()
    {
        Route::get('/welcome', function () {
		    return ['welcome to api'];
		});



        // jwt-auth middleware

        Route::group(['middleware' => 'jwt-auth'], function ()
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
