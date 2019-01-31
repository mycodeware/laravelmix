<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

/**
 *
 * designing route
 *
 */
	
	
	Route::get('login', function () {
	    return view('home');
	})->name('login');

	Route::get('/social', function () {
	    return view('social');
	});
	Route::get('/category', function () {
	    return view('categories');
	});
	Route::get('/device-management', function () {
	    return view('devices-management');
	});
	Route::get('/myaccount', function () {
	    return view('account-details');
	});
	Route::get('/result-not-found', function () {
	    return view('result-not-found');
	});
	Route::get('/search', function () {
	    return view('search-result');
	});
	Route::get('/watchlist', function () {
	    return view('watchlist');
	});
	Route::get('/content-detail', function () {
	    return view('content-detail');
	});

	Route::get('/bundle', function () {
	    return view('bundle');
	});
	Route::get('/terms-and-condition', function () {
	    return view('terms');
	});

	Route::get('/privacy-policy', function () {
	    return view('privacy-policy');
	});
	Route::get('/payment', function () {
	    return view('payment');
	});

	/**design route end**/

	Route::get('/', function () {
	    return view('home');
	});
	Route::match(['post','get'],'get/stores',[
	    'as' => 'getStores',
	    'uses' => 'StoreController@getStores'
	    ]
	); 
	
	Route::match(['post','get'],'get/stores',[
	    'as' => 'getStores',
	    'uses' => 'StoreController@getStores'
	    ]
	); 

	Route::match(['post','get'],'member/login',[
	    'as' => 'memberLogin',
	    'uses' => 'MemberController@login'
	    ]
	); 

	Route::match(['post','get'],'member/registration',[
	    'as' => 'memberRegistration',
	    'uses' => 'MemberController@registration'
	    ]
	); 

	Route::match(['post','get'],'member/profile',[
	    'as' => 'memberProfile',
	    'uses' => 'MemberController@getProfile'
	    ]
	); 



	Route::match(['post','get'],'member/account',[
	    'as' => 'memberAccount',
	    'uses' => 'MemberController@memberAccount'
	    ]
	);

	Route::match(['post','get'],'member/forgetPassword',[
	    'as' => 'forgetPassword',
	    'uses' => 'MemberController@forgetPassword'
	    ]
	); 

	Route::match(['post','get'],'member/emailVerification',[
	    'as' => 'emailVerification',
	    'uses' => 'MemberController@emailVerification'
	    ]
	);

	Route::match(['post','get'],'member/setNewPassword',[
	    'as' => 'setNewPassword',
	    'uses' => 'MemberController@setNewPassword'
	    ]
	); 

	Route::match(['post','get'],'member/changePassword',[
		'as' => 'changePassword',
		'uses'=>'MemberController@changePassword'
		]

	);

	Route::match(['post','get'],'member/generateOtp',[
		'as' => 'resendOtp',
		'uses' => 'MemberController@forgetPassword'
		]
	);

		Route::match(['post','get'],'detectDevice',[
		'as' => 'detectDevice',
		'uses' => 'MemberController@detectDevice'
		]
	);
	Route::get('/logout',function(){
		\Auth::logout();
		 return Redirect::to('/');
	})->name('logout');

	Route::match(['post','get'],'addDevice',[
	    'as' => 'addDevice',
	    'uses' => 'MemberController@addDevice'
	    ]
	);

	Route::match(['post','get'],'getDevice',[
	    'as' => 'getDevice',
	    'uses' => 'MemberController@getDevice'
	    ]
	);

	Route::match(['post','get'],'detectDevice',[
	    'as' => 'detectDevice',
	    'uses' => 'MemberController@detectDevice'
	    ]
	);

	Route::match(['post','get'],'getProfile',[
	    'as' => 'getProfile',
	    'uses' => 'MemberController@getProfile'
	    ]
	);

	Route::match(['post','get'],'updateProfile',[
	    'as' => 'updateProfile',
	    'uses' => 'MemberController@updateProfile'
	    ]
	);


	// Fallback Route
	Route::fallback(function () {
	     return view('result-not-found');
	});
