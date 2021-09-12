<?php

include_models([
    'User'
]);

class UserSeeder extends Seeder
{
    public function run()
    {
        User::create([
            'name' => 'user',
            'username' => 'user01',
            'password' => '1234'
        ]);
    }
}
