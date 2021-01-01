<?php
    // 經常使用的方法定義在這邊

    $dd_options = [
        'max_object_depth' => 10,
        'tab_size' => 4,
        'pre_style' => 'background:#333; font-size:16px; color:#fff; padding:10px;',
        'styles' => [
            // 'error' => 'color: #ff0000;',
            // 'index' => 'color: #a71d5d;',
            // 'integer' => 'color: #56aaff',
            // 'string' => 'color: #f3a999',
            // 'boolean' => 'color: #ffe100',
            // 'default' => 'color: #2cef57;',
            'index' => 'color: #f3a999',
            'error' => 'color: #ff0000;',
            'object' => 'color: #56aaff;',
            'default' => 'color: #2cef57;',
            'fold' => 'color: #ffe100;',
        ]
    ];

    function dd(...$params){
        global $dd_options;
        foreach($params as $param){
            echo '<pre style="'.$dd_options['pre_style'].'">';
            ddp($param);
            echo '</pre>';
        }
        exit;
    }

    function dump(...$params){
        global $dd_options;
        foreach($params as $param){
            echo '<pre style="'.$dd_options['pre_style'].'">';
            ddp($param);
            echo '</pre>';
        }
    }

    function ddp($param, $tab=0, $object_depth=1, $close=false){
        global $dd_options;

        if($object_depth >= $dd_options['max_object_depth']){
            return;
        }
        if(is_object($param)){
            $count = is_iterable($param) ? iterator_count($param) : count(get_object_vars($param));
            $custom_params = [];
            if($param instanceof DB){
                $object_depth++;
                foreach(get_class_methods($param) as $method){
                    if(in_array($method, get_class_methods('DB'))){
                        continue;
                    }
                    try {
                        $custom_params[$method] = call_user_func([$param, $method]);
                    } catch (\Throwable $th) {
                    }
                }
            }
            $count += count($custom_params);

            echo '<span style="'.$dd_options['styles']['object'].'">';
            echo 'object('.get_class($param).')#'.spl_object_id($param).' ('.$count.') ';
            echo "</span>";
            if($close){
                echo '<span style="'.$dd_options['styles']['fold'].'cursor:pointer;" onclick="this.innerHTML=this.innerHTML===\'(Open)<br>\'?\'(Close) ';
                echo '<span style=color:#fff>{</span>';
                echo '<br>\':\'(Open)<br>\';this.nextSibling.style.display=this.nextSibling.style.display===\'none\'?\'block\':\'none\';">(Open)<br></span><div style="display: none;">';
            } else if($object_depth <= $dd_options['max_object_depth'] - 1){
                echo str_repeat(' ', $tab).'{ <br>';
            }
            if($object_depth >= $dd_options['max_object_depth']){
                echo '<span style="'.$dd_options['styles']['error'].'">'.str_repeat(' ', $tab+$dd_options['tab_size']) .' dump object depth exceeded. </span><br>';
            }else{
                foreach($param as $key => $value){
                    echo str_repeat(' ', $tab+$dd_options['tab_size']) . '<span style="'.$dd_options['styles']['index'].'">["'.$key. '"]</span> => ';
                    ddp($value, $tab+$dd_options['tab_size'], $object_depth, true);
                }
                foreach($custom_params as $key => $value){
                    echo str_repeat(' ', $tab+$dd_options['tab_size']) . '<span style="'.$dd_options['styles']['index'].'">["'.$key. '"]</span> => ';
                    ddp($value, $tab+$dd_options['tab_size'], $object_depth, true);
                }
                if(!$close)
                    echo str_repeat(' ', $tab).'}<br>';
            }
            if($close){
                echo str_repeat(' ', $tab).'}</div>';
            }
        } else if(is_array($param)){
            echo '<span style="'.$dd_options['styles']['object'].'">';
            echo "array(".count($param).") ";
            echo '</span>';
            if($close){
                echo '<span style="'.$dd_options['styles']['fold'].'cursor:pointer;" onclick="this.innerHTML=this.innerHTML===\'(Open)<br>\'?\'(Close) ';
                echo '<span style=color:#fff>{</span>';
                echo '<br>\':\'(Open)<br>\';this.nextSibling.style.display=this.nextSibling.style.display===\'none\'?\'block\':\'none\';">(Open)<br></span><div style="display: none;">';
            } else {
                echo '{ <br>';
            }
            foreach($param as $key => $value){
                    echo str_repeat(' ', $tab+$dd_options['tab_size']) . '<span style="'.$dd_options['styles']['index'].'">["'.$key. '"]</span> => ';
                ddp($value, $tab+$dd_options['tab_size'], $object_depth, true);
            }
            if($close){
                echo str_repeat(' ', $tab).'}</div>';
            }else{
                echo str_repeat(' ', $tab).'}<br>';
            }
        } else {
            $type = gettype($param);
            $style = $dd_options['styles'][$type] ?? $dd_options['styles']['default'];
            echo '<span style="'.$style.'">';
            var_dump($param);
            echo '</span>';
        }
    }

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

    function array_fetch($array, $keys){
        $output = [];
        foreach($array as $row){
            $output[count($output)] = [];
            foreach($keys as $key){
                array_copy($output[count($output)-1], $row, $key);
            }
        }
        return $output;
    }

    function array_only($array, $keys){
        return array_fetch([$array], $keys);
    }

    function array_get($array, $key){
        $current_key = explode('.', $key)[0];
        $key = implode('.', array_slice(explode('.', $key), 1));

        if($key == ""){
            return $array[$current_key];
        }

        if(!containsKey($array, $current_key))
            return false;

        return array_get($array[$current_key], $key);
    }

    function array_copy(&$a, &$b, $key){
        $current_key = explode('.', $key)[0];
        $key = implode('.', array_slice(explode('.', $key), 1));

        if($key == ""){
            $a[$current_key] = $b[$current_key];
            return;
        }

        if(!containsKey($a, $current_key))
            $a[$current_key] = [];

        if(!containsKey($b, $current_key))
            $b[$current_key] = [];

        array_copy($a[$current_key], $b[$current_key], $key);
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

     function url($path){

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
            return '/'.$path;
        }

        return $path;
     }

     function old($key, $default=''){
        $value = $_SESSION['input'][$key] ?? $default;
        unset($_SESSION['input'][$key]);
        return $value;
     }