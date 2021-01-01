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
            if(!self::check())
                return null;

            $data = json_decode(json_encode($_SESSION['user']), true);
            $class = $data['__PHP_Incomplete_Class_Name'];
            unset($data['__PHP_Incomplete_Class_Name']);
            return new $class($data);
        }

        public static function get_user_class(){
            if(!self::check())
                return null;

            return get_class(self::user());
        }
    }