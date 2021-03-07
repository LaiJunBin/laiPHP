<?php

    interface Migration {
        public function up();
        public function down();
    }