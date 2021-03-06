<?php

    function uncamelize($camelCaps,$separator='_')
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', "$1" . $separator . "$2", $camelCaps));
    }

    class Validator{
        private $status;
        private $data;

        public function __construct($status){
            $this->status = $status;
            $this->data = [];
        }

        public static function require($input, $require_fields){
            $validation = new Validator(true);
            if($input instanceof Collection){
                $input = $input->to_array();
            }
            return self::_require($input, $require_fields, $validation, $validation->data);
            return $validation;
        }

        private static function _require($input, $require_fields, $validation, &$data){

            if($validation->fails() || is_array($input) && count($require_fields) > count($input)){
                $validation->status = false;
                return $validation;
            }

            foreach($require_fields as $require_field_key => $require_field_value){
                
                if(is_array($require_field_value)){
                    // foreach($require_field_value as $field){
                    if(array_key_exists($require_field_key, $input)){
                        if($require_field_key === 0){
                            foreach($input as $next_input){
                                $data[] = [];
                                Validator::_require($next_input, $require_field_value, $validation, $data[count($data)-1]);
                            }
                        }else{
                            if(!isset($validation->data[uncamelize($require_field_key)]))
                                $validation->data[uncamelize($require_field_key)] = [];

                            if(is_array($input[$require_field_key]) && count($input[$require_field_key]) > 0)
                                Validator::_require($input[$require_field_key], $require_field_value, $validation, $validation->data[uncamelize($require_field_key)]);
                        }
                    }else{
                        $validation->status = false;
                        return $validation;
                    }
                    // }
                } else {
                    if(!array_key_exists($require_field_value, $input)){
                        $validation->status = false;
                        return $validation;
                    }
                    $data[uncamelize($require_field_value)] = $input[$require_field_value];
                }
            }

            return $validation;
        }

        public function fails(){
            return $this->status === false;
        }

        public function data(){
            return $this->data;
        }
    }