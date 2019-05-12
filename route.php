<?php

    class Route{
        static $routes = [];

        static function get($url,$action){
            self::process("get",$url,$action);
        }

        static function post($url,$action){
            self::process("post",$url,$action);
        }

        static function put($url,$action){
            self::process("put",$url,$action);
        }

        static function patch($url,$action){
            self::process("patch",$url,$action);
        }

        static function delete($url,$action){
            self::process("delete",$url,$action);
        }

        static function process($method,$url,$action){
            if(!containsKey(self::$routes,$method)){
                self::$routes[$method] = [];
            }
            list($script,$function) = explode('@',$action);


            $url = explode('/',$url);
            clearEmpty($url);
            $url = implode('/',$url);

            $pattern = preg_replace("/{.[^}]*}/","(.*)",$url);
            $pattern = str_replace('/','\/',$pattern);
            $pattern = str_replace('?','\?',$pattern);
            $pattern = '/^'.$pattern.'$/';

            $url = explode('/',$url);
            clearEmpty($url);
            $url_count = count($url);

            array_push(self::$routes[$method],[
                'script'=>$script,
                'function'=>$function,
                'pattern'=>$pattern,
                'len'=>$url_count
            ]);

        }

        static function hasUri($url,$method='get'){
            $isCorrect = false;
            $url = explode('/',$url);
            clearEmpty($url);
            $url_count = count($url);
            $url = implode('/',$url);
            foreach(static::$routes[$method] ?? [] as $route){
                if($url_count == $route['len'] && preg_match($route['pattern'],$url)){
                    $isCorrect = true;
                    break;
                }
            }
            return $isCorrect;
        }
    }