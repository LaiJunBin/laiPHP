
    // ------- CLI Generate Routing (start) -------
    Route::group("/%s", function(){
        Route::get("/", "%s@index");
        Route::get("/{id}", "%s@show");
        Route::get("/create", "%s@create");
        Route::post("/", "%s@store");
        Route::get("/{id}/edit", "%s@edit");
        Route::patch("/{id}", "%s@update");
        Route::delete("/{id}", "%s@delete");
    });
    // ------- CLI Generate Routing (end) -------
