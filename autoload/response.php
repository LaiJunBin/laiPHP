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
            $filenames = glob("./app/views/{$file}.lai.php");

            // require($filenames[0]);
            // return $this;

            if(count($filenames) == 0){
                $filenames = glob("./app/views/{$file}.php");
                require($filenames[0]);
                return $this;
            } else {
                echo "<!DOCTYPE html>";
                header('Content-Type: text/plain');
                // header('Content-Type: text/html;charset=UTF-8');
                $html_text = file_get_contents($filenames[0]);
                preg_match_all('/{{\s+([^}]*)\s+}}/', $html_text, $matches);
                for($i = 0; $i < count($matches[0]); $i++){
                    $html_text = str_replace($matches[0][$i], htmlspecialchars(eval("return {$matches[1][$i]};")), $html_text);
                }

                preg_match_all('/@([^{]*{([^}]*)})/', $html_text, $expressions);
                while(count($expressions[0]) > 0){
                    $temp = '';
                    $expressions[1][0] = str_replace($expressions[2][0], "\$temp .= \"{$expressions[2][0]}\";", $expressions[1][0]);
                    dd($expressions);
                    eval($expressions[1][0]);
                    $html_text = str_replace($expressions[0][0], $temp, $html_text);
                    preg_match_all('/@([^{]*{([^}]*)})/', $html_text, $expressions);
                    dd($expressions);
                }
                $dom = DomDocument::loadHtml($html_text);
                $shtml = simplexml_import_dom($dom);
                $html_text = $shtml->asxml();
                echo $html_text;
                return $this;
            }

            throw new Exception('View Template Not Found.');
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