<?php

    class Blueprint {
        private $alters = [];
        private $columns = [];
        public function id($name='id', $length=11){
            $this->columns[] = "`{$name}` int({$length}) NOT NULL";
            $this->alters[] = "ADD PRIMARY KEY (`{$name}`)";
            $this->alters[] = "MODIFY `{$name}` int({$length}) NOT NULL AUTO_INCREMENT";
        }

        public function string($name, $length=100){
            $this->columns[] = "`{$name}` varchar({$length}) NOT NULL";
            return $this;
        }
        
        public function text($name){
            $this->columns[] = "`{$name}` text NOT NULL";
            return $this;
        }

        public function integer($name, $length=11){
            $this->columns[] = "`{$name}` int({$length}) NOT NULL";
            return $this;
        }

        public function timestamps(){
            $this->columns[] = "`created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP";
        }

        public function nullable(){
            $this->columns[count($this->columns)-1] = str_replace('NOT NULL', '', $this->columns[count($this->columns)-1]);
            return $this;
        }

        public function default($default){
            $this->columns[count($this->columns)-1] .= " DEFAULT '{$default}'";
            return $this;
        }

        public function getAlters(){
            return $this->alters;
        }

        public function getColumnSql(){
            return implode(',', $this->columns);
        }
    }