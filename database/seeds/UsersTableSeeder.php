<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     *
     * Insert demo data Users table
     */
    public function run()
    {
        // install for customer
        $first_name = session('first_name');
        $last_name = session('last_name');
        $email = session('email');
        $password = session('password');



        DB::table('users')->insert([

            'first_name' => $first_name,
            'last_name' => $last_name,
            'email' => $email,
            'password' => Hash::make($password),
            'verified' => 1,
            'is_admin' => 1,
            'user_type' => 'staff',
            'branch_id' =>1,
            'token' => ''
        ]);
    }
}
