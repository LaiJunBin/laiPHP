<?php

    class CreateUsersTable implements Migration {
        public function up() {
            Schema::create('users', function(Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('username');
                $table->string('password');
                $table->timestamps();
            });
        }

        public function down() {
            Schema::dropIfExists('users');
        }
    }