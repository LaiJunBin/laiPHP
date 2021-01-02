<?php

    class Auth {
        public static function login($user){
            $_SESSION['user'] = [
                'class' => get_class($user),
                'data' => $user->to_array()
            ];
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

            $data = $_SESSION['user']['data'];
            $class = $_SESSION['user']['class'];
            return new $class($data);
        }

        public static function get_user_class(){
            if(!self::check())
                return null;

            return get_class(self::user());
        }
    }