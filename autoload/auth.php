<?php

    class Auth {
        public static function login($user){
            session()->put('user', [
                'class' => get_class($user),
                'data' => $user->to_array()
            ]);
        }

        public static function logout(){
            session()->forget('user');
        }

        public static function check(){
            return session()->has('user');
        }

        public static function user(){
            if(!self::check())
                return null;

            $data = session()->get('user.data');
            $class = session()->get('user.class');
            if(!class_exists($class)){
                include_model($class);
            }
            return new $class($data);
        }

        public static function get_user_class(){
            if(!self::check())
                return null;

            return get_class(self::user());
        }
    }