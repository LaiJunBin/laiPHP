<?php

    class VarDumper {
        private static $options = [
            'max_object_depth' => 7,
            'max_string_length' => 2000,
            'tab_size' => 4,
            'pre_style' => 'background:#333; font-size:16px; color:#fff; padding:10px;',
            'styles' => [
                'modifier' => 'color: #ed99f3;',
                'index' => 'color: #f3a999;',
                'error' => 'color: #ff0000;',
                'object' => 'color: #56aaff;',
                'default' => 'color: #2cef57;',
                'unknown' => 'color: #f00;',
                'fold' => 'color: #ffe100;',
                'type' => 'color: #ffe100;',
                'parameters' => 'color: #ef8f2c;',
            ],
            'hide_type' => [
                'unknown',
                'parameters'
            ]
        ];

        public static function dump(){
            echo '<pre style="'.self::$options['pre_style'].'">';
            foreach (func_get_args() as $var) {
                self::dumpVar($var);
            }
            echo '</pre>';
        }

        private static function dumpVar($var, $tab=0, $object_depth=1, $close=false, $hideArrayTitle=false, $defaultType=null){

            if(is_object($var) || is_array($var)){
                if($object_depth >= self::$options['max_object_depth']){
                    echo '<span style="'.self::$options['styles']['error'].'">dump object depth exceeded. </span><br>';
                    return;
                }
            }

            if(is_object($var)){
                $reflectionObject = new ReflectionObject($var);
                $properties = $reflectionObject->getProperties();
                $methods = $reflectionObject->getMethods();

                echo '<span style="'.self::$options['styles']['object'].'">';
                echo 'object('.get_class($var).')#'.spl_object_id($var).' ('.count($properties).') ';
                echo "</span>";

                if($close){
                    echo '<span style="'.self::$options['styles']['fold'].'cursor:pointer;" onclick="this.innerHTML=this.innerHTML===\'(Open)<br>\'?\'(Close) ';
                    echo '<span style=color:#fff>{</span>';
                    echo '<br>\':\'(Open)<br>\';this.nextSibling.style.display=this.nextSibling.style.display===\'none\'?\'block\':\'none\';">(Open)<br></span><div style="display: none;">';
                } else if($object_depth <= self::$options['max_object_depth'] - 1){
                    echo str_repeat(' ', $tab).'{ <br>';
                }

                foreach($properties as $property){
                    $property->setAccessible(true);
                    $value = $property->getValue($var);
                    $key = $property->getName();
                    $modifier = '+';
                    if($property->isPrivate()){
                        $modifier = '-';
                    } else if($property->isProtected()){
                        $modifier = '#';
                    }
                    echo str_repeat(' ', $tab+self::$options['tab_size']);
                    echo '<span style="'.self::$options['styles']['modifier'].'">'.$modifier.'</span>';
                    echo '<span style="'.self::$options['styles']['index'].'">["';
                    if($property->isStatic()){
                        echo '<span style="text-decoration: underline;">';
                    }
                    echo $key;
                    if($property->isStatic()){
                        echo '</span>';
                    }
                    echo '"]</span> => ';
                    self::dumpVar($value, $tab+self::$options['tab_size'], $object_depth+1, true, $hideArrayTitle);
                }

                foreach($methods as $method){
                    if($reflectionObject->isIterable() && in_array($method->getName(), [
                        'rewind',
                        'current',
                        'key',
                        'next',
                        'valid',
                    ])){
                        continue;
                    }
                    $key = $method->getName();
                    if(stripos($key, '__') === 0 || !$method->isPublic()){
                        continue;
                    }
                    $closure = $method->getClosure($var);
                    $modifier = '+';
                    echo str_repeat(' ', $tab+self::$options['tab_size']);
                    echo '<span style="'.self::$options['styles']['modifier'].'">'.$modifier.'</span>';
                    echo '<span style="'.self::$options['styles']['index'].'">["';
                    if($method->isStatic()){
                        echo '<span style="text-decoration: underline;">';
                    }
                    echo $key;
                    if($method->isStatic()){
                        echo '</span>';
                    }

                    echo '"]</span> => ';
                    echo '<span style="'.self::$options['styles']['object'].'">';
                    echo 'object(Closure)#'.spl_object_id($method).' ('.$method->getNumberOfParameters().') ';
                    echo "</span>";

                    if($method->hasReturnType()){
                        echo '-> ';
                        echo '<span style="'.self::$options['styles']['type'].'">';
                        echo $method->getReturnType()->getName();
                        echo " </span>";
                    }

                    $value = [
                        'parameters' => array_map(function($parameter){
                            return $parameter->getName();
                        }, $method->getParameters())
                    ];

                    $value['parameters']['__type'] = 'parameters';
                    try {
                        ob_start();
                        try {
                            DB::beginTransaction();
                        } catch (\Throwable $th) {
                        }
                        if(in_array($method->getName(), ['beginTransaction', 'rollBack'])){
                            throw new Throwable();
                        }
                        $result = $method->invoke(clone $var);
                        try {
                            DB::rollBack();
                        } catch (\Throwable $th) {
                        }
                        ob_clean();
                        $value['result'] = $result;
                        echo '<span style="'.self::$options['styles']['fold'].'cursor:pointer;" onclick="this.innerHTML=this.innerHTML===\'(Open)<br>\'?\'(Close) ';
                        echo '<span style=color:#fff>{</span>';
                        echo '<br>\':\'(Open)<br>\';this.nextSibling.style.display=this.nextSibling.style.display===\'none\'?\'block\':\'none\';">(Open)<br></span><div style="display: none;">';
                        $tab += self::$options['tab_size'];
                        echo str_repeat(' ', $tab+self::$options['tab_size']);
                        self::dumpVar($value, $tab+self::$options['tab_size'], $object_depth+1, false, false);
                        $tab -= self::$options['tab_size'];
                        echo '</span>';
                        echo str_repeat(' ', $tab+self::$options['tab_size']).'}</div>';
                    } catch (\Throwable $th) {
                        try {
                            DB::rollBack();
                        } catch (\Throwable $th) {
                        }
                        $value['result'] = 'Unknown result.';
                        $value['__type'] = 'unknown';
                        echo '<span style="'.self::$options['styles']['fold'].'cursor:pointer;" onclick="this.innerHTML=this.innerHTML===\'(Open)<br>\'?\'(Close) ';
                        echo '<span style=color:#fff>{</span>';
                        echo '<br>\':\'(Open)<br>\';this.nextSibling.style.display=this.nextSibling.style.display===\'none\'?\'block\':\'none\';">(Open)<br></span><div style="display: none;">';
                        $tab += self::$options['tab_size'];
                        echo str_repeat(' ', $tab+self::$options['tab_size']);
                        self::dumpVar($value, $tab+self::$options['tab_size'], $object_depth+1, false, true);
                        $tab -= self::$options['tab_size'];
                        echo '</span>';
                        echo str_repeat(' ', $tab+self::$options['tab_size']).'}</div>';
                    }
                }

                if(!$close)
                    echo str_repeat(' ', $tab).'}<br>';

                if($close){
                    echo str_repeat(' ', $tab).'}</div>';
                }

            } else if(is_array($var)){
                if(isset($var['__type'])){
                    $type = $var['__type'];
                    unset($var['__type']);
                } else {
                    $defaultType = null;
                }

                echo '<span style="'.self::$options['styles']['object'].'">';

                $openText = 'Open';
                if(!$hideArrayTitle){
                    echo "array(".count($var).") ";
                }
                echo '</span>';

                if($close){
                    echo '<span style="'.self::$options['styles']['fold'].'cursor:pointer;" onclick="this.innerHTML=this.innerHTML===\'('.$openText.')<br>\'?\'(Close) ';
                    echo '<span style=color:#fff>{</span>';
                    echo '<br>\':\'('.$openText.')<br>\';this.nextSibling.style.display=this.nextSibling.style.display===\'none\'?\'block\':\'none\';">('.$openText.')<br></span><div style="display: none;">';
                } else {
                    echo '{ <br>';
                }

                foreach($var as $key => $value){
                    echo str_repeat(' ', $tab+self::$options['tab_size']) . '<span style="'.self::$options['styles']['index'].'">["'.$key. '"]</span> => ';
                    self::dumpVar($value, $tab+self::$options['tab_size'], $object_depth+1, true, $hideArrayTitle, $type ?? $defaultType);
                }
                if($close){
                    echo str_repeat(' ', $tab).'}</div>';
                }else{
                    echo str_repeat(' ', $tab).'}<br>';
                }
            } else {
                $type = $defaultType ?? gettype($var);

                $style = self::$options['styles'][$type] ?? self::$options['styles']['default'];
                if(mb_strlen($var) <= self::$options['max_string_length']){
                    echo '<span style="'.$style.'">';
                    if(in_array($type, self::$options['hide_type'])){
                        echo $var.'<br>';
                    } else {
                        var_dump($var);
                    }
                    echo '</span>';
                }else{
                    echo '<span style="'.self::$options['styles']['fold'].'cursor:pointer;" onclick="this.innerHTML=this.innerHTML===\'(Open..'.strlen($var).')<br>\'?\'(Close) ';
                    echo '<br>\':\'(Open..'.strlen($var).')<br>\';this.nextSibling.style.display=this.nextSibling.style.display===\'none\'?\'block\':\'none\';">(Open..'.strlen($var).')<br></span><div style="display: none;">';
                    echo '<span style="'.$style.'">';
                    var_dump($var);
                    echo '</span>';
                    echo str_repeat(' ', $tab).'</div>';
                }
            }
        }
    }

    if (!function_exists('dd')) {
        function dd(){
            VarDumper::dump(...func_get_args());
            die;
        }
    }

    if (!function_exists('dump')) {
        function dump(){
            VarDumper::dump(...func_get_args());
        }
    }