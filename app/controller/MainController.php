<?php

    function index(Request $request){
        return Response()->view('welcome', [
            'id' => $request->id
        ]);
    }

    function test(){
        return Response("test");
    }

    function api(){
        return Response()->json();
    }