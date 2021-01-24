<?php

    class URL {
        public $url;
        public function __construct($path=''){
            $is_cli_server = php_sapi_name() == 'cli-server';
            $path = array_filter(explode('/', $path), function($x){
                return $x !== '.';
            });

            clearEmpty($path);
            $path = implode('/', $path);

            if(!$is_cli_server){
                $current_dir = str_replace('\\','/',getcwd());
                $root = $_SERVER['DOCUMENT_ROOT'];
                $except_url = explode('/',str_replace($root,'',$current_dir));

                clearEmpty($except_url);
                $path = explode('/', ('/'.implode('/', $except_url).'/'.$path));
                clearEmpty($path);
                $path = implode('/', $path);
            }

            $this->url = '/'.$path;
            // if($this->url === $_SERVER['REQUEST_URI'] || $this->url.'/' === $_SERVER['REQUEST_URI']){
            //     $this->url .= '/';
            // }
        }

        public function current(){
            return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        }

        public function previous(){
            return $_SERVER['HTTP_REFERER'] ?? url('/');
        }

        public function __toString(){
            return $this->url;
        }
    }