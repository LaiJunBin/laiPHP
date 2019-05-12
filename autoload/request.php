<?php

    class Request {

        private $__status;
        private $__method;

        function __get($name) {
            if($name === 'status')
                return $this->__status;
            else if($name === 'method')
                return $this->__method;

            user_error("Invalid property: " . __CLASS__ . "->$name");
        }
        function __set($name, $value) {
            user_error("Can't set property: " . __CLASS__ . "->$name");
        }

        public function __construct() {
            $this->__status = $_SERVER['REDIRECT_STATUS'] ?? 200;
            $this->__method = $_SERVER['REQUEST_METHOD'];
        }

        public function all(){
            return $this->json() ?? (count($_POST) == 0 ? $_GET : $_POST);
        }

        public function json(){
            $raw_data = file_get_contents('PHP://input');
            $json_data = json_decode($raw_data, true);
            return $json_data;
        }

        public function get(){
            return $_GET;
        }

        public function post(){
            return $_POST;
        }
    }