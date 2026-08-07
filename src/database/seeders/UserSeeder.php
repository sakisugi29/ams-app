<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $param = [
            'name' =>'user1',
            'email' => 'user1@example.com',
            'password' => Hash::make('password'),
            'role' =>'staff',
            'email_verified_at' => now(),
        ];
        DB::table('users')->insert($param);
        $param = [
            'name' =>'user2',
            'email' => 'user2@example.com',
            'password' => Hash::make('password'),
            'role' =>'staff',
            'email_verified_at' => now(),
        ];
        DB::table('users')->insert($param);
        $param = [
            'name' =>'user3',
            'email' => 'user3@example.com',
            'password' => Hash::make('password'),
            'role' =>'admin',
            'email_verified_at' => now(),
        ];
        DB::table('users')->insert($param);
    }
}
