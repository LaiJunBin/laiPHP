<?php

    class Collection implements Iterator {
        private $items;
        public function __construct($items = [], $deep=false) {
            if($items instanceof Collection){
                $items = $items->to_array();
            }

            if(!is_array($items)){
                throw new Exception('items type error.');
            }
            if($deep){
                $this->items = array_map_recursive($items, function($item){
                    if(is_array($item)){
                        return new Collection($item);
                    }
                    return $item;
                })->items;
            }else{
                $this->items = $items;
            }
        }

        public function __get($name) {
            return $this->items[$name] ?? null;
        }

        public function __set($name, $value) {
            $this->items[$name] = $value;
        }

        public function __unset($name){
            unset($this->items[$name]);
        }

        public function __call($name, $arguments){
            switch($name){
                case 'find':
                    return $this->findObject($arguments[0]);
                case 'count':
                    return $this->countItems();
            }
        }

        public static function __callStatic($name, $arguments){
            dump($name);
        }

        public function clear(){
            $this->items = [];
        }

        public function set($data){
            $this->clear();
            if($data instanceof Collection){
                $data = $data->to_array();
            }

            array_walk_recursive($data, function($v, $k){
                $this->$k = $v;
            });
        }

        public function assign($items){
            foreach($items as $k => $v){
                $this->$k = $v;
            }
        }

        // public function get($index=null){
        //     if($index===null)
        //         return $this;

        //     if($index >= count($this->items)){
        //         return null;
        //     }

        //     return $this->items[$index];
        // }

        public function first($default=null){
            return $this->items[0] ?? $default;
        }

        public function last($default=null){
            return $this->items[count($this->items)] ?? $default;
        }

        public function countItems(){
            return count($this->items);
        }

        public function includes($item){
            return in_array($item, $this->items);
        }

        public function map($func){
            return new Collection(array_map($func, $this->items));
        }

        public function filter($func){
            return new Collection(array_filter($this->items, $func));
        }

        public function recursive($func){
            $res = $func($this);
            if(!$res)
                return;

            if(get_class($res) === Collection::class){
                return $res->map(function($d) use($func){
                    return $d->recursive($func);
                });
            }
            return $res->recursive($func);
        }

        public function sum(){
            return array_sum($this->items);
        }

        public function findObject($func){
            foreach($this as $k => $v){
                if($func($v, $k)){
                    return $v;
                }
            }
        }

        public function forEach($func){
            foreach($this as $k => $v){
                $func($v, $k);
            }
        }

        public function fetch(...$keys){
            return new Collection(array_fetch($this->to_array(), ...$keys), true);
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

        public function flat(){
            $output = [];
            foreach($this as $values){
                foreach($values as $value){
                    $output[] = $value;
                }
            }
            return new Collection($output);
        }

        public function join($glue=' '){
            return implode($glue, $this->to_array());
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