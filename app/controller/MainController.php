<?php

    function index(){
        view('welcome');
    }

    function get($id){
        view('welcome',[
            'id'=>$id
        ]);
    }