<?php

    function index(){
        return Response()->view('welcome');
    }

    function get($id){
        return Response()->view('welcome',[
            'id'=>$id
        ]);
    }

    function test(){
        return Response("test");
    }