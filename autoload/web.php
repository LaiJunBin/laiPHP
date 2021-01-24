<?php

    include('./route.php');


    // Route File
    // Route::method('url/{params}','controller@function');
    // Example:
    Route::get('/test', 'MainController@test');

    Route::group('/api', function(){
        Route::middleware('auth', function(){
            Route::get('/', 'MainController@api');
        });
    });

    Route::get('/','MainController@index');
    Route::get('/{id}','MainController@index');