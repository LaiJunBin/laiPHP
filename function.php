<?php
    // 經常使用的方法定義在這邊

    function keys($array){
        return array_keys($array);
    }

    function values($array){
        return array_values($array);
    }

    function containsKey($array,$data){
        return array_search($data,keys($array)) !==false;
    }

    function contains($array,$data){
        return array_search($data,$array) !==false;
    }

    function array_fetch($array, ...$keys){
        if(count($keys) === 0){
            throw new Exception('keys empty.');
        } else if (count($keys) === 1){
            if(is_array($keys[0])){
                $keys = $keys[0];
            }else{
                $keys = [$keys[0]];
            }
        } else {
            $keys = $keys;
        }

        $output = [];
        foreach($array as $row){
            $output[count($output)] = [];
            foreach($keys as $key){
                array_copy($output[count($output)-1], $row, $key);
            }
        }
        return $output;
    }

    function array_only($array, ...$keys){
        return array_fetch([$array], ...$keys)[0];
    }

    function array_get($array, $key, $default=null){
        $current_key = explode('.', $key)[0];
        $key = implode('.', array_slice(explode('.', $key), 1));

        if($key == ""){
            return $array[$current_key] ?? $default;
        }

        if(!containsKey($array, $current_key))
            return false;

        return array_get($array[$current_key], $key, $default);
    }

    function array_forget(&$array, $key, $exception=false){
        $current_key = explode('.', $key)[0];
        $key = implode('.', array_slice(explode('.', $key), 1));

        if($key == ""){
            if(array_key_exists($current_key, $array))
                unset($array[$current_key]);
            else if($exception)
                throw new Exception('array key not found.');
        }

        if(!containsKey($array, $current_key)){
            if($exception)
                throw new Exception('array key not found.');

            return false;
        }

        array_forget($array[$current_key], $key, $exception);
    }

    function array_copy(&$a, &$b, $key){
        $current_key = explode('.', $key)[0];
        $key = implode('.', array_slice(explode('.', $key), 1));

        if($key == ""){
            $a[$current_key] = $b[$current_key] ?? null;
            return;
        }

        if(!containsKey($a, $current_key))
            $a[$current_key] = [];

        if(!containsKey($b, $current_key))
            $b[$current_key] = [];

        array_copy($a[$current_key], $b[$current_key], $key);
    }

    function array_map_recursive($array, $func) {
        return $func(array_map(function($item) use($func){
            return is_array($item)? array_map_recursive($item, $func) : $func($item);
        }, $array));
    }

    function str_replace_first($from, $to, $content){
        $from = '/'.preg_quote($from, '/').'/';
        return preg_replace($from, $to, $content, 1);
    }

    function clearEmpty(&$array){
        $array = values(array_filter($array,function($d){
            return $d !="";
        }));
    }

    function Response($res=null){
        return new Response($res);
    }

    function get_mime_type($filename) {
        $idx = explode('.', $filename );
        $count_explode = count($idx);
        $idx = strtolower($idx[$count_explode-1]);

        $mimet = array(
            'txt' => 'text/plain',
            'htm' => 'text/html',
            'html' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'swf' => 'application/x-shockwave-flash',
            'flv' => 'video/x-flv',

            // images
            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml',

            // archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msdownload',
            'cab' => 'application/vnd.ms-cab-compressed',

            // audio/video
            'mp3' => 'audio/mpeg',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',

            // adobe
            'pdf' => 'application/pdf',
            'psd' => 'image/vnd.adobe.photoshop',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',

            // ms office
            'doc' => 'application/msword',
            'rtf' => 'application/rtf',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',
            'docx' => 'application/msword',
            'xlsx' => 'application/vnd.ms-excel',
            'pptx' => 'application/vnd.ms-powerpoint',


            // open office
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        );

        if (isset( $mimet[$idx] )) {
         return $mimet[$idx];
        } else {
         return 'application/octet-stream';
        }
     }

     function url($path=''){
        return new URL($path);
     }

     function clean_url($url){
        $url = explode('/', $url);
        clearEmpty($url);
        $url = implode('/', $url);
        return $url;
     }

     function public_path($path){
         return clean_url('public/'.$path);
     }

     function assets_path($path){
        return clean_url('public/assets/'.$path);
     }

     function old($key, $default=''){
        $value = session()->input->$key ?? $default;
        session()->forget('input.'.$key);
        return $value;
     }

     function method_field($method){
         return '<input type="hidden" name="_method" value="'.$method.'">';
     }

     function route($name, $params=[], $parseURL=true){
        $target_route = null;
        foreach(Route::$routes as $routes){
            foreach($routes as $route){
                if(($route->name ?? '') === $name){
                    $target_route = $route;
                    break 2;
                }
            }
        }

        if(!$target_route){
            throw new Exception('route not found.');
        }

        if(count($params) !== count($target_route->params)){
            throw new Exception('route params not match.');
        }

        $uri = $target_route->pattern_uri;

        if(keys($params) === range(0, count($params) - 1)){
            $params = array_combine(values($target_route->params), values($params));
        }

        foreach($target_route->params as $key){
            if(!array_key_exists($key, $params)){
                throw new Exception('route params not match.');
            }
            $uri = str_replace_first('(.*)', $params[$key], $uri);
        }
        $uri = str_replace('\/', '/', $uri);
        if($parseURL){
            return url($uri);
        }

        return $uri;
     }

     function include_model($model){
        $path = 'app/'.$model.'.php';
        include_once($path);
     }

     function include_models($models=[]){
        foreach($models as $model){
            include_model($model);
        }
     }

     function session(){
         return new Session();
     }

     function array_to_model($array, $model){
         return new $model($array);
     }

     function array_to_models($arrays, $model){
         return new Collection(array_map(fn($array) => array_to_model($array, $model), $arrays));
     }