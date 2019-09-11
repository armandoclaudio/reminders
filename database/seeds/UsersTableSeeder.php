<?php

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory('App\User')->create([
            'name' => 'Armando Claudio',
            'email' => 'armando@example.com',
            'password' => bcrypt('secret'),
        ]);
    }
}
