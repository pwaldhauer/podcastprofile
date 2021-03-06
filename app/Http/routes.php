<?php

/*
|--------------------------------------------------------------------------
| Routes File
|--------------------------------------------------------------------------
|
| Here is where you will register all of the routes in an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/feed', 'StaticController@testFeed');

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| This route group applies the "web" middleware group to every route
| it contains. The "web" middleware group is defined in your HTTP
| kernel and includes session state, CSRF protection, and more.
|
*/

Route::group(['middleware' => ['web']], function () {
    Route::get('/', 'StaticController@getIndex')->name('home');

    Route::group(['prefix' => 'auth/twitter', 'as' => 'auth::'], function () {
        Route::get('/', 'Auth\AuthController@redirectToProvider')->name('login');
        Route::get('/callback', 'Auth\AuthController@handleProviderCallbackAsRedirect')->name('callback');
        Route::get('/callback/json', 'Auth\AuthController@handleProviderCallbackAsJson')->name('callback::json');
        Route::get('/logout', 'Auth\AuthController@logout')->name('logout');
    });

    Route::group(['middleware' => ['auth']], function () {
        Route::get('/settings', 'StaticController@getSettings')->name('settings');
        Route::get('/admin', 'StaticController@getAdmin')->name('admin');
    });

    Route::group(['prefix' => 'api/1', 'as' => 'api::'], function () {
        Route::group(['middleware' => ['auth']], function () {
            Route::post('update', 'ProfileApiController@postProfile')->name('profile');
            Route::post('update/image', 'ProfileApiController@postProfileImage')->name('profile::image');
            Route::post('podcasts', 'ProfileApiController@postUpdatePodcasts')->name('podcasts');
            Route::post('upload/rss', 'ProfileApiController@postPodcastByRss')->name('postPodcastByRss');
            Route::post('upload/opml', 'ProfileApiController@postPodcastsByOpml')->name('postPodcastsByOpml');
        });
    });

    Route::get('/top', 'StaticController@getTop')->name('top');    

    // last
    Route::get('/{handle}', 'StaticController@getProfile')->name('profile');

});
