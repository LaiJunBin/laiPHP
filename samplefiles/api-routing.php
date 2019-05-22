
    // ------- CLI Generate Routing (start) -------
    Route::group("/%s", function(){
        Route::get("/", "%s@list_all");
        Route::get("/{id}", "%s@get");
        Route::post("/", "%s@create");
        Route::patch("/{id}", "%s@update");
        Route::delete("/{id}", "%s@delete");
    });
    // ------- CLI Generate Routing (end) -------
