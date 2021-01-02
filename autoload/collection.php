<?php

    class Collection implements Iterator {
        private $items;
        public function __construct($items = []) {
            if(!is_array($items)){
                throw new Exception('items type error.');
            }
            $this->items = $items;
        }

        function __get($name) {
            return $this->items[$name] ?? null;
        }

        function __set($name, $value) {
            $this->items[$name] = $value;
        }

        function __unset($name){
            unset($this->items[$name]);
        }

        function clear(){
            $this->items = [];
        }

        function set($data){
            $this->clear();
            if($data instanceof Collection){
                $data = $data->to_array();
            }

            array_walk_recursive($data, function($v, $k){
                $this->$k = $v;
            });
        }

        // public function get($index=null){
        //     if($index===null)
        //         return $this;

        //     if($index >= count($this->items)){
        //         return null;
        //     }

        //     return $this->items[$index];
        // }

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

        public function fetch(...$keys){
            return new Collection(array_fetch($this->to_array(), ...$keys));
        }

        public function only(...$keys){
            return new Collection(array_only($this->to_array(), ...$keys));
        }

        public function to_array(){
            array_walk_recursive($this->items, function(&$items){
                $items = $items->items ?? $items;
            });
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