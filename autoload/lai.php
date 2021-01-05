<?php

    class Lai {
        public static function decryptFile($filename, $params) {
            $html_array = [];
            $html_file = fopen($filename, 'r');
            while(($line = fgets($html_file)) !== false){
                $html_array[] = $line;
            }
            fclose($html_file);

            return self::decryptHTML($html_array, $params);
        }

        public static function decryptHTML($html_array, $params){
            self::_extends($html_array);
            $html_text = null;
            while($html_text !== implode(PHP_EOL, $html_array)){
                $html_text = implode(PHP_EOL, $html_array);
                self::_hashtag($html_array, $params);
                self::_decrypt_for_expression($html_array, $params);
                self::_include($html_array, $params);
                self::_yield($html_array, $params);
                self::_section($html_array, $params);
                self::_decrypt_if_expression($html_array, $params);
                self::_decrypt($html_array, $params);
            }

            preg_match_all('/(?=!)(!{{\s*([^}]*)\s*}})|({{\s*([^}]*)\s*}})/', $html_text, $matches);
            foreach($params as $key =>$value){
                $$key = $value;
            }

            for($i = 0; $i < count($matches[0]); $i++){
                if($matches[1][$i]){
                    try {
                        $syntax = "return {$matches[2][$i]} ?? '';";
                        $html_text = str_replace($matches[1][$i], eval($syntax), $html_text);
                    } catch (\Throwable $th) {
                        $err_type = get_class($th);
                        throw new $err_type($matches[2][$i]);
                    }
                }else{
                    try {
                        $syntax = "return {$matches[4][$i]} ?? '';";
                        $html_text = str_replace($matches[3][$i], htmlspecialchars(eval($syntax)), $html_text);
                    } catch (\Throwable $th) {
                        $err_type = get_class($th);
                        throw new $err_type($matches[4][$i]);
                    }
                }
            }

            return $html_text;
        }

        private static function _extends(&$html_array){
            $check_extends = true;
            while($check_extends){
                $check_extends = false;
                for($i = 0; $i < count($html_array); $i++){
                    if(mb_strpos(trim($html_array[$i]), '@extends') === 0){
                        $check_extends = true;
                        $extend_file = trim(self::get_condition($html_array[$i]),'\'');
                        $template_dir = ['app', 'views'];
                        array_push($template_dir, ...explode('.', $extend_file));
                        $file = implode('/', $template_dir).'.lai.php';
                        if(!file_exists($file)){
                            throw new Error('extend template error!, template not found.');
                        }
                        $html_file = fopen($file, 'r');
                        $array = [];
                        while(($line = fgets($html_file)) !== false){
                            $array[] = $line;
                        }
                        fclose($html_file);
                        array_splice($html_array, $i, 1, $array);
                        break;
                        // $html_array = $array;
                    }
                }

            }
        }

        private static function _include(&$html_array, &$params){
            $html_text=  implode(PHP_EOL, $html_array);
            foreach($params as $key =>$value){
                $$key = $value;
            }
            preg_match_all('/(?=@include\([^\b\)\[]+\))@include\(([^\b\)\[]+)\)|@include\(([^\b]+?\])[^\b]*?\)/', $html_text, $matches);
            while(count($matches[0])){
                for($i = 0; $i < count($matches[0]); $i++){
                    $match = $matches[1][$i] | ($matches[2][$i] ?? '');
                    $match = str_replace(PHP_EOL, '', $match);
                    preg_match_all('/(?=^[\'\"])[\'\"]([^\s]+)[\'\"]|^([^\s]+)/', $match, $arguments);
                    $include_file = $arguments[1][0] | $arguments[2][0];
                    $match = str_replace_first($arguments[0][0], '', $match);
                    $match = trim(str_replace_first(',', '', $match));
                    if($match){
                        preg_match_all('/([^\'](\$([\w\->]+(\([^)]*\))*)*))/', $match, $variables);

                        try {
                            $assign_params = eval('return '.$match.';');
                            foreach($assign_params as $k => $v){
                                $params[$k] = $v;
                                $$k = $v;
                            }
                        } catch (\Throwable $th) {
                            foreach($variables[2] as $variable){
                                try {
                                    $param = eval('return '.$variable.';');
                                    $match = str_replace_first($variable, "'".$param."'", $match);
                                } catch (\Throwable $th) {
                                    $pk = 'param_'.bin2hex(random_bytes(5));
                                    $params[$pk] = eval('return '.$variable.';');
                                    $$pk = $params[$pk];
                                    $match = str_replace_first($variable, "$".$pk, $match);
                                }
                            }
                        }

                        // $assign_params = eval('return '.$match.';');
                        // $pk = 'param_'.bin2hex(random_bytes(5));
                        // $params[$pk] = eval('return '.$match.';');
                        // $$pk = $params[$pk];
                        // // $match = preg_replace('/[\$\(\)]/', '\\\$0', $match);
                        // $pattern = '('.preg_replace('/[\$\(\)]/', '\\\$0', $match).')';
                        // $temp[$params['for1']] = preg_replace('/'.$pattern.'/', '$'.$pk, $temp[$params['for1']]);


                        // foreach($assign_params as $k => $v){
                        //     $params[$k] = $v;
                        //     $$k = $v;
                        // }
                    }
                    $template_dir = ['app', 'views'];
                    array_push($template_dir, ...explode('.', $include_file));
                    $file = implode('/', $template_dir).'.lai.php';
                    if(!file_exists($file)){
                        throw new Error('include template error!, template not found.');
                    }

                    $include_html_array = explode(PHP_EOL, file_get_contents($file));
                    $include_html = self::decryptHTML($include_html_array, $params);
                    $html_text = str_replace_first($matches[0][$i], $include_html, $html_text);
                }
                preg_match_all('/@include\(([^\b)]+)\)/', $html_text, $matches);
            }

            $html_array = explode(PHP_EOL, $html_text);
        }

        private static function _hashtag(&$html_array, &$params){
            $err_code = 0;
            set_error_handler(function ($errNo, $errStr) use(&$err_code){
                if (strpos($errStr, 'Use of undefined constant ') === 0) {
                    $err_code = 1;
                } else {
                    return false;
                }
            });

            for($i = 0; $i < count($html_array); $i++){
                $html = trim($html_array[$i]);
                if(strpos($html, '#') === 0){
                    $syntax = '@'.mb_substr($html, 1).';';
                    try {
                        $err_code = 0;
                        eval($syntax);
                        if($err_code === 0)
                            $html_array[$i] = '';
                    } catch (\Throwable $th) {
                    }
                }
            }

            $ignore = [
                'html_array',
                'params',
                'err_code',
                'i',
                'html',
                'syntax'
            ];
            foreach(get_defined_vars() as $key => $value){
                if(!in_array($key, $ignore)){
                    $params[$key] = $value;
                }
            }

            restore_error_handler();
        }

        private static function _yield(&$html_array, &$params){
            for($i = 0; $i < count($html_array); $i++){
                if(mb_strpos(trim($html_array[$i]), '@yield') !== false){
                    $yield_name = self::get_condition($html_array[$i]);
                    $variable = array_map(function($v){
                        return trim(trim($v), '\'');
                    }, explode(',', $yield_name));
                    $html_array[$i] = preg_replace("/@yield\(".$variable[0]."[^)]*\)/", '{{ $yield_'. $variable[0]. ' }}', $html_array[$i]);
                    if(count($variable) == 2){
                        $params['yield_'.$variable[0]] = $variable[1];
                    }
                }
            }
        }

        private static function _section(&$html_array, &$params){
            for($i = 0; $i < count($html_array); $i++){
                if(mb_strpos(trim($html_array[$i]), '@section') !== false){
                    $section_name = self::get_condition($html_array[$i]);
                    $variable = array_map(function($v){
                        return trim(trim($v), '\'');
                    }, explode(',', $section_name));

                    if(count($variable) == 1){
                        $stack = [];
                        for($j = $i; $j < count($html_array); $j++){
                            preg_match_all('/{/', $html_array[$j], $left);
                            if(count($left[0]) > 0)
                                array_push($stack, ...$left[0]);

                            preg_match_all('/}/', $html_array[$j], $right);
                            array_splice($stack, 0, count($right[0]));

                            if(count($stack) == 0){
                                $temp = array_slice($html_array, $i+1, $j-$i-1);

                                array_splice($html_array, $i, $j-$i+1, []);

                                for($k = 0; $k < count($html_array); $k++){
                                    if(mb_strpos(trim($html_array[$k]), '{{ $yield_'.$section_name.' }}') !== false){
                                        array_splice($html_array, $k, 1, $temp);
                                    }
                                }
                                break;
                            }
                        }
                    }else if(count($variable) == 2){
                        $params['yield_'.$variable[0]] = $variable[1];
                        array_splice($html_array, $i, 1, []);
                    }
                }
            }
        }

        private static function _decrypt_for_expression(&$html_array, &$params){
            foreach($params as $key =>$value){
                $$key = $value;
            }

            $origin_keys = array_keys($params);
            $local_params = [];
            foreach($origin_keys as $key){
                $local_params[$key] = $params[$key];
            }

            for($i = 0; $i < count($html_array); $i++){
                if(mb_strpos(trim($html_array[$i]), '@foreach') === 0){
                    $stack = [];
                    for($j = $i; $j < count($html_array); $j++){
                        preg_match_all('/{/', $html_array[$j], $left);
                        if(count($left[0]) > 0)
                            array_push($stack, ...$left[0]);

                        preg_match_all('/}/', $html_array[$j], $right);
                        array_splice($stack, 0, count($right[0]));

                        if(count($stack) == 0){
                            $condition = self::get_condition($html_array[$i]);
                            foreach($local_params as $k => $v){
                                if(!in_array($k, $origin_keys)){
                                    unset($local_params[$k]);
                                }
                            }
                            list($start, $end) = [$i, $j];
                            $temp = array_slice($html_array, $i+1, $j-$i-1);
                            $array = [];
                            $index_variable = trim(mb_substr($condition, mb_strpos($condition, ' as')+3));
                            $arrow_index = mb_strpos($index_variable, '=>');
                            if($arrow_index){
                                $index_k = trim(mb_substr($index_variable, 0, $arrow_index));
                                $index_v = trim(mb_substr($index_variable, $arrow_index+2));
                                $syntax = ('foreach('.$condition.'){ $local_params[mb_substr($index_k,1)] = '.$index_k.';$local_params[mb_substr($index_v,1)] = '.$index_v.'; $res=self::for_assign_variable($temp, $local_params, [$index_k, $index_v]); array_push($array, ...$res["array"]); }');
                            }else{
                                $syntax = ('foreach('.$condition.'){ $local_params[mb_substr($index_variable,1)] = '.$index_variable.'; $res = self::for_assign_variable($temp, $local_params, $index_variable); array_push($array, ...$res["array"]);}');
                            }
                            preg_match_all('/\$+([\w]+)/', $condition, $variables);
                            eval($syntax);
                            if(isset($res)){
                                foreach($res['params'] as $key => $value){
                                    if(in_array($key, $variables[1])){
                                        continue;
                                    }
                                    $$key = $value;
                                    $params[$key] = $value;
                                }
                            }

                            array_splice($html_array, $start, $end-$start+1, $array);
                            $i = -1;
                            break;
                        }
                    }
                }else if(mb_strpos(trim($html_array[$i]), '@for') === 0){
                    $stack = [];
                    for($j = $i; $j < count($html_array); $j++){
                        preg_match_all('/{/', $html_array[$j], $left);
                        if(count($left[0]) > 0)
                            array_push($stack, ...$left[0]);

                        preg_match_all('/}/', $html_array[$j], $right);
                        array_splice($stack, 0, count($right[0]));

                        if(count($stack) == 0){
                            $condition = self::get_condition($html_array[$i]);
                            list($start, $end) = [$i, $j];
                            $temp = array_slice($html_array, $i+1, $j-$i-1);
                            $array = [];
                            $index_variable = trim(mb_substr($condition, 0, mb_strpos($condition, '=')));
                            $syntax = ('for('.$condition.'){ $local_params[mb_substr($index_variable,1)] = '.$index_variable.'; $res=self::for_assign_variable($temp, $local_params, $index_variable); array_push($array, ...$res["array"]); }');
                            eval($syntax);
                            if(isset($res)){
                                foreach($res['params'] as $key => $value){
                                    $$key = $value;
                                    $params[$key] = $value;
                                }
                            }
                            array_splice($html_array, $start, $end-$start+1, $array);
                            $i = -1;
                            break;
                        }
                    }
                }
            }

        }

        private static function _decrypt_if_expression(&$html_array, $params){

            foreach($params as $key =>$value){
                $$key = $value;
            }

            for($i = 0; $i < count($html_array); $i++){
                // convert if else to if + if
                if(preg_match('/}\s*else\s*{/', $html_array[$i])){

                    $stack = [];
                    for($j = $i-1; $j >= 0; $j--){
                        if(mb_strpos(trim($html_array[$j]),'}') === 0){
                            array_push($stack, '}');
                            continue;
                        }
                        if(mb_strpos(trim($html_array[$j]),'@if') === 0){
                            if(count($stack) == 0){
                                $condition = self::get_condition($html_array[$j]);
                                $array = ['}', "@if(!({$condition})){"];
                                array_splice($html_array, $i, 1, $array);
                                break;
                            }else{
                                array_pop($stack);
                            }
                        }
                    }
                }

                // convert else if to if > if
                if(preg_match('/}\s*else if\s*(.*){/', $html_array[$i])){
                    $stack = [];
                    for($j = $i-1; $j >= 0; $j--){
                        if(mb_strpos(trim($html_array[$j]),'}') === 0){
                            array_push($stack, '}');
                            continue;
                        }
                        if(mb_strpos(trim($html_array[$j]),'@if') === 0){
                            if(count($stack) == 0){
                                $condition = self::get_condition($html_array[$j]);
                                $condition2 = self::get_condition($html_array[$i]);
                                $array = ['}', "@if(!({$condition})){"];
                                $array2 = ["@if({$condition2}){"];
                                array_splice($html_array, $i+1,0,$array2);
                                array_splice($html_array, $i, 1, $array);
                                $stack = [];
                                for($k = $i+2; $k < count($html_array); $k++){
                                    preg_match_all('/{/', $html_array[$k], $left);
                                    if(count($left[0]) > 0)
                                        array_push($stack, ...$left[0]);

                                    preg_match_all('/}/', $html_array[$k], $right);
                                    array_splice($stack, 0, count($right[0]));

                                    if(count($stack) == 0){
                                        array_splice($html_array, $k+1, 0, '}');
                                        break;
                                    }
                                }

                                break;
                            }else{
                                array_pop($stack);
                            }
                        }
                    }
                }
            }

        }

        private static function get_expression($html_text){
            preg_match_all('/@([^{]*{([^}]*))/', $html_text, $expressions);
            return $expressions;
        }

        private static function find_brackets($array, $left_bracket, $right_bracket){
            if(!is_array($array)){
                $array = preg_split('//', $array);
                clearEmpty($array);
            }

            $left = -1;
            $right = -1;
            for($i = count($array)-1; $i >= 0; $i--){
                $row = trim($array[$i]);
                if(mb_strpos($row, '@') === 0 && mb_strpos($row, $left_bracket) !== false){
                    $left = $i;
                    break;
                }
            }

            if($left == -1)
                return [-1, -1];

            for($i = $left; $i < count($array); $i++){
                $row = trim($array[$i]);
                if(mb_strpos($row, $right_bracket) === 0){
                    $right = $i;
                    break;
                }
            }

            if($right == -1)
                return [-1, -1];

            return [$left, $right];
        }

        private static function get_condition($expression){
            $left = mb_strpos($expression, '(');
            $right = mb_strripos($expression, ')');
            return mb_substr($expression, $left+1, $right-$left-1);
        }

        private static function _decrypt(&$html_array, $params){
            list($left, $right) = self::find_brackets($html_array, '{', '}');

            while($left != -1 && $right != -1){
                $expression = trim($html_array[$left]);
                if(mb_strpos($expression, '@') === 0){
                    $function_name = '_'.trim(mb_substr($expression, 1, mb_strpos($expression, '(')-1));
                    self::$function_name($html_array, $expression, $left, $right, $params);
                }
                list($left, $right) = self::find_brackets($html_array, '{', '}');
            }
        }

        private static function _if(&$html_array, $expression, $left, $right, $params=[]){
            foreach($params as $key =>$value){
                $$key = $value;
            }

            $condition = self::get_condition($expression);
            $condition = eval("return {$condition};");

            if($condition){
                $array = array_slice($html_array, $left+1, $right-$left-1);
                array_splice($html_array, $left+1, $right-$left);
                $html_array[$left] = implode(' ',$array);
            }else{
                array_splice($html_array, $left, $right-$left+1);
            }
        }

        private static function _for(&$html_array, $expression, $left, $right, $params=[]){
            foreach($params as $key =>$value){
                $$key = $value;
            }

            $condition = self::get_condition($expression);
            $temp = array_slice($html_array, $left+1, $right-$left-1);
            $array = [];
            $index_variable = mb_substr($condition, 0, mb_strpos($condition, '=')-1);
            $syntax = ('for('.$condition.'){ $params[mb_substr($index_variable,1)] = '.$index_variable.'; array_push($array, ...self::for_assign_variable($temp, $params)); }');
            eval($syntax);
            array_splice($html_array, $left+1, $right-$left);
            $html_array[$left] = implode(' ',$array);
        }

        private static function for_assign_variable($temp, &$params, $index_variables=[]){
            foreach($params as $key =>$value){
                $$key = $value;
            }

            $array = [];
            if(!is_array($index_variables)){
                $index_variables = [$index_variables];
            }

            for($params['for1'] = 0; $params['for1'] < count($temp); $params['for1']++){
                $err_code = 0;
                set_error_handler(function ($errNo, $errStr) use(&$err_code){
                    $err_code = 1;
                });
                preg_match_all('/(?=!)(!{{\s*([^}]*)\s*}})|({{\s*([^}]*)\s*}})/', $temp[$params['for1']], $matches);
                for($i = 0; $i < count($matches[0]); $i++){
                    $err_code = 0;

                    if($matches[1][$i]){
                        try {
                            $syntax = "return {$matches[2][$i]};";
                            $res = eval($syntax);
                            if($err_code === 0){
                                $temp[$params['for1']] = str_replace($matches[1][$i], $res, $temp[$params['for1']]);
                            }
                        } catch (\Throwable $th) {
                        }
                    }else{
                        try {
                            $syntax = "return {$matches[4][$i]};";
                            $res = eval($syntax);
                            if($err_code === 0){
                                $temp[$params['for1']] = str_replace($matches[3][$i], htmlspecialchars($res), $temp[$params['for1']]);
                            }
                        } catch (\Throwable $th) {
                        }
                    }
                }

                restore_error_handler();

                preg_match_all('/([^\'](\$([\w\->]+(\([^)]*\))*)*))/', $temp[$params['for1']], $matches);
                foreach($matches[2] as $match){
                    preg_match_all('/\$([^\W\->]+)/', $match, $variables);

                    foreach($variables[1] as $index_variable){
                        if(array_key_exists($index_variable, $params)){
                            try {
                                $pattern = '/('.preg_replace('/[\$\(\)]/', '\\\$0', $match).')([\s\W])/';
                                $temp[$params['for1']] = preg_replace($pattern, "'".eval('return @'.$match.';')."'$2", $temp[$params['for1']]);
                            } catch (\Throwable $th) {
                                $pk = 'param_'.bin2hex(random_bytes(5));
                                $params[$pk] = eval('return '.$match.';');
                                $$pk = $params[$pk];
                                // $match = preg_replace('/[\$\(\)]/', '\\\$0', $match);
                                $pattern = '('.preg_replace('/[\$\(\)]/', '\\\$0', $match).')';
                                $temp[$params['for1']] = preg_replace('/'.$pattern.'/', '$'.$pk, $temp[$params['for1']]);
                            }
                        }
                    }

                }



                preg_match_all('/{{\s*([^}]*)\s*}}/', $temp[$params['for1']], $matches);
                if(count($matches[1])){
                    preg_match_all('/[^\']*(\$+[\w]+)/', $temp[$params['for1']], $variables);

                    if(count($variables[1]) > 0 && array_search(false, array_map(function($v) use($params){
                        return array_key_exists(mb_substr($v, 1), $params);
                    }, $variables[1])) === false){
                        try {
                            $syntax = "return ".$variables[1][0].';';
                            $pattern = '/('.preg_replace('/[\$\(\)]/', '\\\$0', $variables[1][0]).')([\s\W])/';
                            $temp[$params['for1']] = preg_replace($pattern, "'".eval($syntax)."'$2", $temp[$params['for1']]);
                        } catch (\Throwable $th) {
                            $pk = 'param_'.bin2hex(random_bytes(5));
                            $params[$pk] = eval('return '.$match.';');
                            $$pk = $params[$pk];
                            $match = preg_replace('/[\$\(\)]/', '\\\$0', $match);
                            $pattern = '('.$match.')([\s\W])([\s\W])';
                            $temp[$params['for1']] = preg_replace('/'.$pattern.'/', '$'.$pk.'$2$3', $temp[$params['for1']]);
                        }
                    }
                }

                $array[] = $temp[$params['for1']];

            }

            return ['array' => $array, 'params' => $params];
        }

    }