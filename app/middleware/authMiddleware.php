<?php

    class AuthMiddleware extends Middleware{
        function handle($request){
            return true;
        }
    }