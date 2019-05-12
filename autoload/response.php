<?php
    class Response{
        public function __construct($res = null) {
            echo $res;
            return $this;
        }

        public function json($json_data){
            header('Content-Type: application/json');
            echo json_encode($json_data);
            return $this;
        }

        public function code($code=200){
            http_response_code($code);
            $this->log($code);
            return $this;
        }

        public function view($file,$params=[]){
            foreach($params as $key =>$value){
                $$key = $value;
            }

            $file = str_replace('.','/',$file);
            $filename = glob("./app/views/{$file}*");

            if(count($filename) == 0)
                throw new Exception('View Template Not Found.');

            require($filename[0]);
            return $this;
        }

        public function redirect($url){
            $url = explode('/',$url);
            clearEmpty($url);
            $url = implode('/',$url);

            header("location:{$url}");
            return $this;
        }

        public function log($status_code=200){
            $addr = $_SERVER['REMOTE_ADDR'];
            $port = $_SERVER['REMOTE_PORT'];
            $request_uri = $_SERVER['REQUEST_URI'];
            $log = $addr.':'.$port.' ['.$status_code.']: '.$request_uri;
            error_log($log);
        }
    }