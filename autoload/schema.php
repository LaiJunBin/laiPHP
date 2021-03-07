<?php

    class Schema {
        public static function create($table, $schema){
            $blueprint = new Blueprint();
            $schema($blueprint);
            $alterSql = implode(PHP_EOL, array_map(function($sql) use($table){
                return "ALTER TABLE `{$table}` " . $sql . ';';
            }, $blueprint->getAlters()));
            $sql = "
CREATE TABLE `{$table}` (
{$blueprint->getColumnSql()}
);

{$alterSql}
            ";

            DB::execute($sql);
        }

        public static function dropIfExists($table){
            DB::execute("DROP TABLE IF EXISTS {$table};");
        }
    }