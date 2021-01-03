<?php

    class Route{
        static $routes = [];
        static $route_table = [];

        private static $prefix = '';
        private static $current_middleware = [];

        public function __construct($params=[]){
            foreach($params as $key => $value){
                $this->$key = $value;
            }
        }

        public function name($name){
            $this->name = $name;
            self::$route_table[$this->method][count(self::$route_table[$this->method])-1]['name'] = $name;
            return $this;
        }

        public function middleware($middleware, $func=null){
            if($func === null){
                array_push($this->middleware, $middleware);
                self::$route_table[$this->method][count(self::$route_table[$this->method])-1]['middleware'] = implode(', ', $this->middleware);
                return $this;
            }
            array_push(self::$current_middleware, $middleware);
            $func();
            array_pop(self::$current_middleware);
        }

        static function get($url,$action){
            return self::process("get", $url, $action);
        }

        static function post($url,$action){
            self::process("post", $url, $action);
        }

        static function put($url,$action){
            self::process("put", $url, $action);
        }

        static function patch($url,$action){
            self::process("patch", $url, $action);
        }

        static function delete($url,$action){
            self::process("delete", $url, $action);
        }

        static function group($prefix, $func){
            self::$prefix .= '/'.$prefix;
            $func();
            self::$prefix = mb_substr(self::$prefix, 0, mb_strlen(self::$prefix)-mb_strlen($prefix)-1);
        }

        // static function middlewareStatic($middleware, $func){
        //     array_push(self::$current_middleware, $middleware);
        //     $func();
        //     array_pop(self::$current_middleware);
        // }

        static function process($method,$url,$action){
            if(!containsKey(self::$routes,$method)){
                self::$routes[$method] = [];
                self::$route_table[$method] = [];
            }
            list($script,$function) = explode('@',$action);

            $url = self::$prefix.'/'.$url;
            $url = explode('/',$url);
            clearEmpty($url);
            $url = implode('/',$url);

            array_push(self::$route_table[$method], [
                'method' => $method,
                'url' => '/'.$url,
                'action' => $action,
                'middleware' => implode(', ', self::$current_middleware),
                'name' => ''
            ]);

            preg_match_all("/{(.[^}]*)}/", $url, $params);
            $pattern = preg_replace("/{.[^}]*}/","(.*)",$url);
            $pattern = str_replace('/','\/',$pattern);
            $pattern = str_replace('?','\?',$pattern);
            $pattern = '/^'.$pattern.'$/';

            $url = explode('/',$url);
            clearEmpty($url);
            $url_count = count($url);

            $route = new Route([
                'method' => $method,
                'script'=>$script,
                'function'=>$function,
                'pattern'=>$pattern,
                'len'=>$url_count,
                'middleware' => self::$current_middleware,
                'params' => $params[1]
            ]);
            array_push(self::$routes[$method], $route);
            return $route;
        }

        static function hasUri($url,$method='get'){
            $isCorrect = false;
            $url = explode('/',$url);
            clearEmpty($url);
            $url_count = count($url);
            $url = implode('/',$url);
            foreach(static::$routes[$method] ?? [] as $route){
                if($url_count == $route->len && preg_match($route->pattern, $url)){
                    $isCorrect = true;
                    break;
                }
            }
            return $isCorrect;
        }
    }