<?php

    class DB extends Collection {
        static protected $table = null;
        static protected $id = 'id';
        private static $db = null;
        private $instance = false;

        public function __construct($items = []) {
            if(get_called_class() === 'DB'){
                throw new Exception("can't new DB.");
            }
            parent::__construct($items);
        }

        public function isInstance(){
            return $this->instance;
        }

        public function save(){
            $class = get_called_class();
            $params = $this->to_array();
            if($this->instance){
                self::__callStatic('update', [$params, [static::$id => $this->{static::$id}]]);
            }else{
                $model = $class::create($params);
                $this->set($model);
                $this->instance = true;
            }
        }

        private function updateObject($params){
            static::__callStatic('update', [$params, [static::$id => $this->{static::$id}]]);
            foreach($params as $k => $v){
                $this->$k = $v;
            }
        }

        private function deleteObject(){
            $this->instance = false;
            call_user_func([$this, 'onDelete']);
            static::__callStatic('delete', [[static::$id => $this->{static::$id}]]);
        }

        public function __call($name, $arguments){
            if($this->instance){
                switch($name){
                    case 'update':
                        return $this->updateObject($arguments[0]);
                    case 'delete':
                        return $this->deleteObject();
                }
            }
        }

        public static function __callStatic($name, $arguments){
            switch($name){
                case 'update':
                    return self::updateStatic($arguments[0], $arguments[1]);
                case 'delete':
                    return self::deleteStatic($arguments[0]);
                case 'find':
                    return self::findStatic($arguments[0], $arguments[1] ?? []);
                case 'count':
                    return self::countStatic();
            }
        }

        static function get_table(){
            return static::$table??get_called_class();
        }

        static function create($params, $return=true){
            $table = self::get_table();
            if($params instanceof Collection)
                $params = $params->to_array();

            $keys = implode(',',keys($params));

            $bindKeys = implode(',',array_map(function($key){
                return ':'.$key;
            },keys($params)));

            $sql = "insert into {$table}({$keys}) values({$bindKeys})";

            self::bindAll($sql,$params);
            if($return){
                return self::find(self::$db->lastInsertId());
            }
        }

        static function updateStatic($params,$conditions){

            $table = self::get_table();
            if($params instanceof Collection)
                $params = $params->to_array();
            if($conditions instanceof Collection){
                $conditions = $conditions->to_array();
            }
            $keys = implode(',',array_map(function($key){
                return $key.'=:'.$key;
            },keys($params)));

            $conditionKeys = implode(' and ',array_map(function($key){
                return $key.'=:c'.$key;
            },keys($conditions)));

            $conditions = array_combine(array_map(function($key){
                return 'c'.$key;
            },keys($conditions)),values($conditions));

            $sql = "update {$table} set {$keys} where {$conditionKeys}";

            self::bindAll($sql,array_merge($params,$conditions));
        }

        static function deleteStatic($conditions){
            if($conditions instanceof Collection){
                $conditions = $conditions->to_array();
            }
            $table = self::get_table();

            $conditionKeys = implode(' and ',array_map(function($key){
                return $key.'=:'.$key;
            },keys($conditions)));

            $sql = "delete from {$table} where {$conditionKeys}";

            self::bindAll($sql,$conditions);
        }

        static function get($index=null, $orderby=null){
            if(is_array($index)){
                $orderby = $index;
                $index = null;
            }
            if($orderby instanceof Collection){
                $orderby = $orderby->to_array();
            }
            $class = get_called_class();
            $results = self::select([],$orderby)->fetchAll(PDO::FETCH_ASSOC);
            $array = [];
            foreach($results as $result){
                $instance = new $class($result);
                $instance->instance = true;
                $array[] = $instance;
            }
            if(is_numeric($index)){
                return $array[$index] ?? null;
            }else{
                return new Collection($array);
            }
        }

        static function countStatic(){
            return self::get()->count();
        }

        static function findStatic($conditions,$orderby=null){
            if($conditions instanceof Collection){
                $conditions = $conditions->to_array();
            }
            if(!is_array($conditions)){
                $conditions = [
                    static::$id => $conditions
                ];
            }
            $class = get_called_class();
            $data = self::select($conditions,$orderby)->fetch(PDO::FETCH_ASSOC);
            if(!$data)
                return false;
            $instance = new $class($data);
            $instance->instance = true;
            return $instance;
        }

        static function findall($conditions,$orderby=null){
            if($conditions instanceof Collection){
                $conditions = $conditions->to_array();
            }
            if($orderby instanceof Collection){
                $orderby = $orderby->to_array();
            }
            $class = get_called_class();
            $results = self::select($conditions,$orderby)->fetchAll(PDO::FETCH_ASSOC);
            $array = [];
            foreach($results as $result){
                $instance = new $class($result);
                $instance->instance = true;
                $array[] = $instance;
            }
            return new Collection($array);
        }

        static function contains($conditions,$orderby=null){
            return self::select($conditions,$orderby)->fetch(PDO::FETCH_ASSOC) !==false;
        }

        static function select($conditions,$orderby=null){
            if($conditions instanceof Collection){
                $conditions = $conditions->to_array();
            }
            if($orderby instanceof Collection){
                $orderby = $orderby->to_array();
            }

            $table = self::get_table();
            $sql = "select * from {$table}";

            if(count($conditions) > 0){
                $conditionKeys = implode(' and ',array_map(function($key){
                    return $key.'=:'.$key;
                },keys($conditions)));

                $sql .= " where {$conditionKeys}";
            }

            if($orderby !=null){
                $by = implode(' ',array_map(function($key) use($orderby){
                    return $key.' '.$orderby[$key];
                },keys($orderby)));
                $sql.= ' order by '.$by;
            }

            return self::bindAll($sql,$conditions);
        }

        static function bindAll($sql,$params){
            self::connection_db();

            $query = self::$db->prepare($sql);
            foreach($params as $key => $value){
                $query->bindValue($key,$value);
            }
            $query->execute();

            if($query->errorinfo()[2] != ""){
                echo $sql.'<br>'.$query->errorinfo()[2];
                exit;
            }

            return $query;
        }

        static function execute($sql, $params=[]){
            if(self::get_table() !== 'DB')
                return;

            self::connection_db();
            $query = self::$db->prepare($sql);
            foreach($params as $key => $value){
                $query->bindValue($key,$value);
            }
            $query->execute();

            if($query->errorinfo()[2] != ""){
                echo $sql.'<br>'.$query->errorinfo()[2];
                exit;
            }

            return $query;
        }

        private static function connection_db(){
            if(self::$db == null){
                self::$db = new PDO("mysql:host=".HOST.";dbname=".DATA_BASE,USER_NAME,PASS_WORD);
                self::$db->exec('set names utf8');
            }
        }

        protected function hasOne($table, $foreign_key=null, $id=null){
            if($foreign_key === null){
                $foreign_key = strtolower($table).'_id';
            }
            if($id === null){
                $id = static::$id;
            }

            include_once('app/'.$table.'.php');
            return @$table::findStatic([$id => $this->$foreign_key]);
        }

        protected function hasMany($table, $foreign_key=null, $id=null){
            if($foreign_key === null){
                if($this->through ?? false){
                    $foreign_key = strtolower($table).'_id';
                }else{
                    $foreign_key = strtolower(get_called_class()).'_id';
                }
            }
            if($id === null){
                $id = static::$id;
            }
            include_once('app/'.$table.'.php');
            if($this->through ?? false){
                $results = $this->data->map(function($tmp) use($table, $foreign_key, $id){
                    $row = $table::find([$id => $tmp->$foreign_key]);
                    $row->_tmp = $tmp;
                    return $row;
                });
                return $results;
            }else{
                return @$table::findall([$foreign_key => $this->$id]);
            }
        }

        protected function through($table, $foreign_key=null, $id=null){
            if($foreign_key === null){
                $foreign_key = strtolower(get_called_class()).'_id';
            }
            if($id === null){
                $id = static::$id;
            }

            include_once('app/'.$table.'.php');
            return new $table([
                'through' => true,
                'data' => @$table::findall([$foreign_key => $this->$id])
            ]);
        }
    }
