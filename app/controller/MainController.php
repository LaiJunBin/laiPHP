<?php

    function index(){
        return Response()->view('welcome');
    }

    function test(){
        return Response("test");
    }

    function api(){
        return Response()->json();
    }