<?php

use Illuminate\Support\Facades\Route;


Route::group(['middleware' => 'guest', 'namespace' => 'General'], function () {
    Route::post('login', 'GeneralController@login')->name('login_post');
    Route::get('login', 'GeneralController@Panel_Login')->name('login');
    Route::get('forgot_password', 'GeneralController@Panel_Pass_Forget')->name('forgot_password');
    Route::post('forgot_password', 'GeneralController@ForgetPassword')->name('forgot_password_post');
});

Route::group(['middleware' => 'Is_Admin'], function () {
    Route::get('/', 'General\GeneralController@Admin_dashboard')->name('dashboard');
    Route::get('/totalusers', 'General\GeneralController@totalusers')->name('totalusers');
    Route::get('/profile', 'General\GeneralController@get_profile')->name('profile');
    Route::post('/profile', 'General\GeneralController@post_profile')->name('post_profile');
    Route::get('/update_password', 'General\GeneralController@get_update_password')->name('get_update_password');
    Route::post('/update_password', 'General\GeneralController@update_password')->name('update_password');
    Route::get('/site_settings', 'General\GeneralController@get_site_settings')->name('get_site_settings');
    Route::post('/site_settings', 'General\GeneralController@site_settings')->name('site_settings');
    Route::group(['namespace' => 'Admin'], function () {
        //        User Module
        Route::get('pdfmerge', 'UsersController@pdfmergefun')->name('pdfmerge');;
        Route::get('getPdfMerge', 'UsersController@getPdfMerge')->name('getPdfMerge');;
        Route::get('user/listing', 'UsersController@listing')->name('user.listing');
        Route::get('user/status_update/{id}', 'UsersController@status_update')->name('user.status_update');
        Route::resource('user', 'UsersController')->except(['create', 'store']);


        Route::get('post/meTooListing', 'PostController@meTooListing')->name('post.meTooListing');
        Route::get('post/solutionListing', 'PostController@solutionListing')->name('post.solutionListing');
        Route::get('post/listing', 'PostController@listing')->name('post.listing');
        Route::resource('post', 'PostController')->except(['create', 'store']);


        Route::get('PostExport/{id?}', 'ExportController@PostExport')->name('PostExport');
        


        //        Content Module
        Route::resource('content', 'ContentController')->except(['show', 'create', 'store', 'destroy']);
        Route::get('content/listing', 'ContentController@listing')->name('content.listing');
      
        

        Route::resource('category', 'CategoryController')->except('show');
        Route::delete('category/user_category_destroy/{id}', 'CategoryController@user_category_destroy')->name('category.user_category_destroy');
        Route::get('category/listing', 'CategoryController@listing')->name('category.listing');
        Route::post('category/category-status-update', 'CategoryController@categoryStatusUpdate')->name('category.category_status_update');

        Route::get('category/user-category-listing', 'CategoryController@userCategoryListing')->name('category.user_categroy_listing');

        Route::get('category/user-category', 'CategoryController@userCategory')->name('category.user_categroy');
        


        //Route::get('category/status_update/{id}', 'CategoryController@status_update')->name('category.status_update');
    });
});
