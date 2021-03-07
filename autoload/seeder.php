<?php

    abstract class Seeder {
        public abstract function run();
        public function call($classes){
            foreach($classes as $class){
                $instance = new $class;
                $instance->run();
                echo $class. ' seeded.'. PHP_EOL;
            }
        }
    }