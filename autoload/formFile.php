<?php

    class FormFile{
        public function __construct($name, $type, $tmp_name, $error, $size){
            $this->name = $name;
            $this->type = $type;
            $this->tmp_name = $tmp_name;
            $this->error = $error;
            $this->size = $size;
            $this->extension = pathinfo($this->name, PATHINFO_EXTENSION);
        }

        public function saveAs($path, $filename=null){
            if($filename == null){
                $filename = str_replace('.', '', microtime(true));
            }

            if(!array_key_exists('extension', pathinfo($filename))){
                $filename .= '.'.$this->extension;
            }

            $path = clean_url($path);
            $this->filename = $filename;
            $this->destination = $path.'/'.$filename;

            move_uploaded_file($this->tmp_name, $this->destination);
            return $this;
        }

    }