<?php

    class %s implements Migration {
        public function up() {
            Schema::create('%s', function(Blueprint $table) {
                $table->id();
                $table->timestamps();
            });
        }

        public function down() {
            Schema::dropIfExists('%s');
        }
    }