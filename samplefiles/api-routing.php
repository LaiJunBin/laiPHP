
    // ------- CLI Generate Routing (start) -------
    Route::get("%s", "%s@list_all");
    Route::get("%s/{id}", "%s@get");
    Route::post("%s", "%s@create");
    Route::patch("%s/{id}", "%s@update");
    Route::delete("%s/{id}", "%s@delete");
    // ------- CLI Generate Routing (end) -------
