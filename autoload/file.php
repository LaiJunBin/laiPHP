<?php

    class File{
        public function __construct($destination){
            $this->destination = $destination;
            foreach(pathinfo($this->destination) as $k => $v){
                $this->$k = $v;
            }
            $this->raw_data = @file_get_contents($this->destination);
        }

        public function delete(){
            @unlink($this->destination);
        }

    }