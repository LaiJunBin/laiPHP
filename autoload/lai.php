<?php

    class Lai {
        public static function decryptFile($filename, $params) {
            foreach($params as $key =>$value){
                $$key = $value;
            }

            $html_array = [];
            $html_file = fopen($filename, 'r');
            while(($line = fgets($html_file)) !== false){
                $html_array[] = $line;
            }
            fclose($html_file);
            for($i = 0; $i < count($html_array); $i++){

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
            }
            self::_decrypt($html_array, $params);

            $html_text = implode(' ', $html_array);
            preg_match_all('/{{\s+([^}]*)\s+}}/', $html_text, $matches);
            for($i = 0; $i < count($matches[0]); $i++){
                $syntax = "return {$matches[1][$i]};";
                $html_text = str_replace($matches[0][$i], htmlspecialchars(eval($syntax)), $html_text);
            }

            return $html_text;
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

        private static function for_assign_variable($temp, $params){
            foreach($params as $key =>$value){
                $$key = $value;
            }

            $array = [];
            for($params['for1'] = 0; $params['for1'] < count($temp); $params['for1']++){
                preg_match_all('/{{\s+([^}]*)\s+}}/', $temp[$params['for1']], $matches);
                $array[$params['for1']] = $temp[$params['for1']];
                for($params['for2'] = 0; $params['for2'] < count($matches[0]); $params['for2']++){
                    $syntax = "return {$matches[1][$params['for2']]};";
                    $array[$params['for1']] = str_replace($matches[0][$params['for2']], htmlspecialchars(eval($syntax)), $array[$params['for1']]);
                }
            }

            return $array;
        }

    }