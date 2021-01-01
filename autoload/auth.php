<?php

    class Auth {
        public static function login($user){
            $_SESSION['user'] = $user;
        }

        public static function logout(){
            unset($_SESSION['user']);
        }

        public static function check(){
            return isset($_SESSION['user']);
        }

        public static function user(){
            return $_SESSION['user'] ?? null;
        }
    }