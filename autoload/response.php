<?php
    class Response{

        public function __construct($res = null) {
            echo $res;
            return $this;
        }

        public function json($json_data=[]){
            header('Content-Type: application/json');
            if($json_data instanceof Collection){
                $json_data = $json_data->to_array();
            }
            echo json_encode($json_data);
            return $this;
        }

        public function code($code=200){
            http_response_code($code);
            $this->log($code);
            return $this;
        }

        public function view($file,$params=[]){
            $file = str_replace('.','/',$file);
            $filenames = glob("./app/views/{$file}.lai.php");

            foreach(GlobalParam::default() as $key => $value){
                if(!array_key_exists($key, $params))
                    $params[$key] = $value;
            }

            foreach(GlobalParam::assign() as $key => $value){
                $params[$key] = $value;
            }

            $params['errors'] = [];
            if(isset($_SESSION['errors'])){
                $params['errors'] = new Collection($_SESSION['errors']);
                unset($_SESSION['errors']);
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
            // $url = explode('/',$url);
            // clearEmpty($url);
            // $url = '/'.implode('/',$url);
            $url = url($url);
            header("location:{$url}");

            return $this;
        }

        public function redirectRoute($route_name, $params){
            return $this->redirect(route($route_name, $params, false));
        }

        public function withInput(Request $request){
            $_SESSION['input'] = $request->all()->to_array();
            return $this;
        }

        public function withErrors($params=[]){
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