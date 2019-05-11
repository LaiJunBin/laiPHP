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

        public function code($code){
            http_response_code($code);
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
    }