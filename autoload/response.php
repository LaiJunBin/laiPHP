<?php
    class Response{

        public function __construct($res = null) {
            echo $res;
            return $this;
        }

        public function json($json_data=[]){
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
            session_start();
            $file = str_replace('.','/',$file);
            $filenames = glob("./app/views/{$file}.lai.php");

            if(isset($_SESSION['errors'])){
                $params['errors'] = $_SESSION['errors'];
                unset($_SESSION['errors']);
            }else{
                $params['errors'] = [];
            }

            if(count($filenames) == 0){
                $filenames = glob("./app/views/{$file}.php");
                if(count($filenames) == 0){
                    throw new Exception('View Template Not Found.');
                }
                require($filenames[0]);
                return $this;
            } else {
                header('Content-Type: text/html;charset=UTF-8');
                // header('Content-Type: text/plain');

                if(in_array('request', keys($params)))
                    throw new \Exception('傳遞模板參數 request 是 保留字!');

                $params['request'] = $GLOBALS['request'];
                $html_text = Lai::decryptFile($filenames[0], $params);

                echo $html_text;
                unset($params['errors']);
                return $this;
            }

            throw new Exception('View Template Not Found.');
        }

        public function redirect($url){
            $url = explode('/',$url);
            clearEmpty($url);
            $url = '/'.implode('/',$url);

            header("location:{$url}");

            return $this;
        }

        public function withErrors($params=[]){
            session_start();
            $_SESSION['errors'] = $params;
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