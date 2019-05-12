<?php

    class DB{
        static protected $table = null;

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

        static function get($orderby=null){
            return self::select([],$orderby)->fetchAll(PDO::FETCH_ASSOC);
        }

        static function find($conditions,$orderby=null){
            return self::select($conditions,$orderby)->fetch(PDO::FETCH_ASSOC);
        }

        static function findall($conditions,$orderby=null){
            return self::select($conditions,$orderby)->fetchAll(PDO::FETCH_ASSOC);
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

            $db = new PDO("mysql:host=".HOST.";dbname=".DATA_BASE,USER_NAME,PASS_WORD);
            $db->exec('set names utf8');

            $query = $db->prepare($sql);
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
    }