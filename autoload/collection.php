<?php

    class Collection implements Iterator {
        public $items;
        public function __construct($items = []) {
            if(!is_array($items)){
                throw new Exception('items type error.');
            }
            $this->items = $items;
        }

        public function get($index=null){
            if($index===null)
                return $this;

            if($index >= count($this->items)){
                return null;
            }

            return $this->items[$index];
        }

        public function first(){
            return $this->items[0];
        }

        public function last(){
            return $this->items[count($this->items)];
        }

        public function count(){
            return count($this->items);
        }

        public function map($func){
            return new Collection(array_map($func, $this->items));
        }

        public function filter($func){
            return new Collection(array_filter($this->items, $func));
        }

        public function to_array(){
            return $this->items;
        }


        public function rewind()
        {
            reset($this->items);
        }

        public function current()
        {
            return current($this->items);
        }

        public function key()
        {
            return key($this->items);
        }

        public function next()
        {
            return next($this->items);
        }

        public function valid()
        {
            $key = key($this->items);
            $item = ($key !== NULL && $key !== FALSE);
            return $item;
        }

    }