<?php

    include('env.php');
    include('function.php');

    foreach(glob('./autoload/*') as $file){
        include($file);
    }

    $current_dir = str_replace('\\','/',getcwd());
    $url = explode('/',$_SERVER['REQUEST_URI']);
    $root = $_SERVER['DOCUMENT_ROOT'];
    $method = strtolower($_SERVER['REQUEST_METHOD']);

    $except_url = explode('/',str_replace($root,'',$current_dir));
    $is_cli_server = php_sapi_name() == 'cli-server';

    clearEmpty($url);
    clearEmpty($except_url);

    if(!$is_cli_server){

        for($i=0;$i<min(count($url),count($except_url));$i++){
            if($url[$i] == $except_url[$i])
                $url[$i] = '';
            else{
                Response()->code(404);
                exit;
            }
        }
    }

    clearEmpty($url);
    $url_count = count($url);
    $url = implode('/',$url);

    if(!preg_match("/^public\/*/",strtolower($url)) && !Route::hasUri($url,$method)){
        Response()->code(404);
        exit;
    }

    $contains_page = false;
    foreach(Route::$routes[$method] ?? [] as $route){
        if($url_count == $route['len'] && preg_match($route['pattern'],$url,$matches)){
            include('app/controller/'.$route['script'].'.php');
            $values = array_map(function($value){
                return '"'.$value.'"';
            },array_slice($matches,1));

            $value = implode(',',$values);

            try {
                $functionText = "{$route['function']}({$value});";
                eval($functionText);
            } catch (\TypeError $th) {
                if($value !== "") $value = ', '.$value;
                $functionText = "{$route['function']}(new Request() {$value});";
                eval($functionText);
            }

            $contains_page = true;
            Response()->log(200);
            break;
        }
    }

    if(!$contains_page){
        if(file_exists($url) && filetype($url) == 'file'){
            header('Content-Type:'.get_mime_type($url));
            echo file_get_contents($url);
        }else{
            Response()->code(404);
        }
    }