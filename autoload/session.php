<?php

    class Session {
        public function __construct(){
            @session_start();
            foreach(new Collection($_SESSION, true) as $key => $value){
                $this->$key = $value;
            }
        }

        public function put($name, $value){
            $_SESSION[$name] = $value;
        }

        public function get($name, $default=null){
            $value = array_get($_SESSION, $name, $default);
            if($value instanceof __PHP_Incomplete_Class){
                throw new Exception('capture __PHP_Incomplete_Class object.');
            }

            return $value;
        }

        public function pop($name, $default=null, $exception=false){
            $value = $this->get($name, $default);
            $this->forget($name, $exception);
            return $value;
        }

        public function has($name){
            return array_get($_SESSION, $name) != null;
        }

        public function forget($name, $exception=false){
            array_forget($_SESSION, $name, $exception);
        }
    }