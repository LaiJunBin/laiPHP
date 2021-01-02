<?php

    class Request {

        private $__status;
        private $__method;
        private $__params;
        private $__uri;

        function __get($name) {
            if($name === 'status')
                return $this->__status;
            else if($name === 'method')
                return $this->__method;
            else if($name === 'uri')
                return $this->__uri;
            else if(containsKey($this->__params, $name))
                return $this->__params[$name];

            user_error("Invalid property: " . __CLASS__ . "->$name");
        }
        function __set($name, $value) {
            user_error("Can't set property: " . __CLASS__ . "->$name");
        }

        public function __construct($params=[]) {
            $this->__status = $_SERVER['REDIRECT_STATUS'] ?? 200;
            $this->__method = $_SERVER['REQUEST_METHOD'];
            $this->__uri = $_SERVER['REQUEST_URI'];
            $this->__params = $params;
        }

        public function all($keys=null){
            $json = $this->json($keys);
            if($json)
                return $json;

            if(count($_POST))
                return $this->post($keys);

            if(count($_GET))
                return $this->get($keys);

            return file_get_contents('PHP://input');
        }

        public function json($keys=null){
            $raw_data = file_get_contents('PHP://input');
            $json_data = json_decode($raw_data, true);
            if(json_last_error() !== JSON_ERROR_NONE)
                return null;

            if($keys){
                if(is_array($keys)){
                    return new Collection(array_only($json_data, $keys));
                } else {
                    return $json_data[$keys] ?? null;
                }
            }

            return new Collection($json_data);
        }

        public function get($keys=null){
            if($keys){
                if(is_array($keys)){
                    return new Collection(array_only($_GET, $keys));
                } else {
                    return $_GET[$keys] ?? null;
                }
            }

            return new Collection($_GET);
        }

        public function post($keys=null){
            if($keys){
                if(is_array($keys)){
                    return new Collection(array_only($_POST, $keys));
                } else {
                    return $_POST[$keys] ?? null;
                }
            }

            return new Collection($_POST);
        }

        public function headers($key=null){
            if($key !== null)
                return apache_request_headers()[$key] ?? null;

            return apache_request_headers();
        }
    }