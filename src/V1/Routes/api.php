<?php

use Illuminate\Http\Request;
use Illuminate\Http\Response;

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

$version = 'v1';

/*
Route::middleware('auth:api')->get('/'.$version, function (Request $request) use ($version) {
    return $request->user();
});
*/

Route::group(
    [
        'prefix' => $version
    ],
    function () use ($version) {

        //AppConfig Cron Route
        Route::get('/Crons/CacheAppConfig', 'Crons\CacheAppConfig@index');

        //Get Auth Route
        Route::post('/Auth/get', 'Auth@get');

        //APi Routes With Auth Header(JWT)
        Route::group(
            [
                'middleware' => [
                    'JwtToken'.$version
                ]
            ], function () use ($version) {
                Route::post('/AppConfig/get', 'AppConfig@get');
                Route::post('/Store/get', 'Store@get');
                Route::post('/Store/getPage', 'Store@getPage');
                Route::post('/IpLocale/get', 'IpLocale@get');
                Route::post('/Registration/custom', 'Registration@custom');
                Route::post('/Registration/social', 'Registration@social');
                Route::post('/Home/sendConsent', 'Home@sendConsent');
                Route::post('/Device/validate', 'Device@validate');
                Route::post('/Device/getList', 'Device@getList');
                Route::post('/Device/add', 'Device@add');
                Route::post('/Device/remove', 'Device@remove');
                Route::post('/Content/getDetail', 'Content@getDetail');
                Route::post('/Content/postPlaybackStatus', 'Content@postPlaybackStatus');
                Route::post('/Content/getPlaybackStatus', 'Content@getPlaybackStatus');
                Route::post('/Category/getData', 'Category@getData');

                //vivek
                Route::post('/AppUser/checkLogin', 'AppUser@checkLogin');
                Route::post('/AppUser/forgotPassword', 'AppUser@forgotPassword');
                Route::post('/AppUser/appResetPassword', 'AppUser@appResetPassword');
                Route::post('/AppUser/socialLogin', 'AppUser@socialLogin');
                Route::post('/AppUser/socialUser', 'AppUser@socialUser');
                 Route::post('/AppUser/userActive', 'AppUser@userActive');

                Route::post('/Favourite/addContent', 'Favourite@addContent');
                Route::post('/Favourite/removeContent', 'Favourite@removeContent');
                Route::post('/Favourite/getIds', 'Favourite@getIds');
                Route::post('/Favourite/getList', 'Favourite@getList');
                
                //vivek

                Route::post('/Portlet/getPagePortlets', 'Portlet@getPagePortlets');
                Route::post('/Portlet/getPortletDetails', 'Portlet@getPortletDetails');
                Route::post('/Dashboard/get', 'Dashboard@get');
                Route::post('/Comment/postComment', 'Comment@postComment');
                Route::post('/Comment/comments', 'Comment@comments');
                Route::post('/Rating/post', 'Rating@post');  
                Route::post('/Rating/get', 'Rating@get');  
            }
        );

        // Fallback Route
        Route::fallback(function () {
            $response = [
                'code' => 400,
                'status' => 'failed',
                'message' => 'Bad request',
            ];
            return \Response::json($response);
        });
    }
);

// Fallback Route
Route::fallback(function () {
    $response = [
        'code' => 400,
        'status' => 'failed',
        'message' => 'Bad request',
    ];
    return Response::json($response);
});
