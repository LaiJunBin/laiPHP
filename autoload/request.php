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

        public function all($keys=null, $default=null){
            $json = $this->json($keys, $default);
            if($json)
                return $json;

            if(count($_POST))
                return $this->post($keys, $default);

            if(count($_GET))
                return $this->get($keys, $default);

            return file_get_contents('PHP://input');
        }

        public function json($keys=null, $default=null){
            $raw_data = file_get_contents('PHP://input');
            $json_data = json_decode($raw_data, true);
            if(json_last_error() !== JSON_ERROR_NONE)
                return null;

            if($keys){
                if(is_array($keys)){
                    return new Collection(array_only($json_data, $keys));
                } else {
                    return $json_data[$keys] ?? $default;
                }
            }

            return new Collection($json_data);
        }

        public function get($keys=null, $default=null){
            if($keys){
                if(is_array($keys)){
                    return new Collection(array_only($_GET, $keys));
                } else {
                    return $_GET[$keys] ?? $default;
                }
            }

            return new Collection($_GET);
        }

        public function post($keys=null, $default=null){
            if($keys){
                if(is_array($keys)){
                    return new Collection(array_only($_POST, $keys));
                } else {
                    return $_POST[$keys] ?? $default;
                }
            }

            return new Collection($_POST);
        }

        public function file($name=null){
            if($name === null){
                $files = [];
                foreach($_FILES as $key => $file){
                    $files[$key] = $this->file($key);
                }
                return $files;
            }

            if(!array_key_exists($name, $_FILES))
                return null;

            if(is_array($_FILES[$name]['name'])){
                $files = [];
                for($i = 0; $i < count($_FILES[$name]['name']); $i++){
                    $files[] = new FormFile(
                        $_FILES[$name]['name'][$i],
                        $_FILES[$name]['type'][$i],
                        $_FILES[$name]['tmp_name'][$i],
                        $_FILES[$name]['error'][$i],
                        $_FILES[$name]['size'][$i],
                    );
                }
                return new Collection($files);
            } else {
                return new FormFile(
                    $_FILES[$name]['name'],
                    $_FILES[$name]['type'],
                    $_FILES[$name]['tmp_name'],
                    $_FILES[$name]['error'],
                    $_FILES[$name]['size'],
                );
            }
        }

        public function headers($key=null){
            if($key !== null)
                return apache_request_headers()[$key] ?? null;

            return apache_request_headers();
        }
    }