<?php

    class DB{
        static protected $table = null;
        private static $db = null;

        public function __construct($items = []) {
            if(get_called_class() === 'DB'){
                throw new Exception("can't new DB.");
            }
            foreach($items as $key => $value){
                $this->$key = $value;
            }
        }

        static function get_table(){
            return static::$table??get_called_class();
        }

        static function create($params){

            $table = self::get_table();

            $keys = implode(',',keys($params));

            $bindKeys = implode(',',array_map(function($key){
                return ':'.$key;
            },keys($params)));

            $sql = "insert into {$table}({$keys}) values({$bindKeys})";

            self::bindAll($sql,$params);
        }

        static function update($params,$conditions){

            $table = self::get_table();


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

        static function delete($conditions){

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
            $class = get_called_class();
            $results = self::select([],$orderby)->fetchAll(PDO::FETCH_ASSOC);
            $array = [];
            foreach($results as $result){
                $instance = new $class($result);
                $array[] = $instance;
            }
            if(is_numeric($index)){
                return $array[$index] ?? null;
            }else{
                return new Collection($array);
            }
        }

        static function find($conditions,$orderby=null){
            $class = get_called_class();
            $instance = new $class(self::select($conditions,$orderby)->fetch(PDO::FETCH_ASSOC));
            return $instance;
        }

        static function findall($conditions,$orderby=null){
            $class = get_called_class();
            $results = self::select($conditions,$orderby)->fetchAll(PDO::FETCH_ASSOC);
            $array = [];
            foreach($results as $result){
                $instance = new $class($result);
                $array[] = $instance;
            }
            return new Collection($array);
        }

        static function contains($conditions,$orderby=null){
            return self::select($conditions,$orderby)->fetch(PDO::FETCH_ASSOC) !==false;
        }

        static function select($conditions,$orderby=null){

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

        protected function hasOne($table, $foreign_key=null, $id='id'){
            if($foreign_key === null){
                $foreign_key = strtolower($table).'_id';
            }
            include_once('app/'.$table.'.php');
            return $table::find([$id => $this->$foreign_key]);
        }

        protected function hasMany($table, $foreign_key=null, $id='id'){
            if($foreign_key === null){
                if($this->through ?? false){
                    $foreign_key = strtolower($table).'_id';
                }else{
                    $foreign_key = strtolower(get_called_class()).'_id';
                }
            }
            include_once('app/'.$table.'.php');
            if($this->through ?? false){
                $results = $this->data->map(function($tmp) use($table, $foreign_key, $id){
                    return $table::find([$id => $tmp->$foreign_key]);
                });
                return $results;
            }else{
                return $table::findall([$foreign_key => $this->$id]);
            }
        }

        protected function through($table, $foreign_key=null, $id='id'){
            if($foreign_key === null){
                $foreign_key = strtolower(get_called_class()).'_id';
            }

            include_once('app/'.$table.'.php');
            return new $table([
                'through' => true,
                'data' => $table::findall([$foreign_key => $this->$id])
            ]);
        }
    }
