<?php

use Illuminate\Support\Facades\Route;


Route::group(['namespace' => 'Api\V1', 'prefix' => 'V1'], function () {

    Route::post('login', 'GuestController@login');
    Route::post('signup', 'GuestController@signup');
    Route::post('forgot_password', 'GuestController@forgot_password');
    Route::get('content/{type}', 'GuestController@content');
    Route::post('forgot_password', 'GuestController@forgot_password');
    Route::post('check_ability', 'GuestController@check_ability');
    Route::post('version_checker', 'GuestController@version_checker');
    Route::get('get_language', 'CategoryController@get_language');
//category
    
    Route::get('get_categories_listing', 'CategoryController@get_categories_listing');
    Route::post('problum_list', 'PostController@problumList');
    Route::post('hastag_list', 'PostController@hastagList');  
    Route::post('post_detail', 'PostController@post_detail'); 
    

    //            Country Selection apis here
    Route::group(['middleware' => 'ApiTokenChecker'], function () {
        Route::group(['prefix' => 'user'], function () {
            Route::get('getProfile', 'UserController@getProfile');
            
            Route::post('update_profile', 'UserController@update_profile');
            Route::post('add_post_problem', 'PostController@addPost');
            Route::post('edit_post_problem', 'PostController@editPost');
            Route::post('add_category', 'PostController@addUserSuggestedCategory');
            
            
            

            Route::post('my_problum_list', 'PostController@myProblumList');
            Route::post('problum_detail', 'PostController@problumDetail');
            Route::post('notification_list', 'PostController@notificationList');
            
            Route::post('solution_list', 'PostController@solutionList');

            Route::post('comment_list', 'PostController@commentList');
            
            
            Route::post('delete_post', 'PostController@deletePost');


            Route::post('add_me_too', 'PostController@addMeeToo');
            Route::post('addComment', 'PostController@addComment');
            Route::post('post_solution', 'PostController@postSolution');
            Route::post('solution_status_update', 'PostController@solutionStatusUpdate');

            
            
            

            Route::get('logout', 'UserController@logout');

        });


    });
});


