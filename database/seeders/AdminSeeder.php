<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $admin = User::create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@konnected.com',
            'phone_no' => '+911234567890',
            'location' => 'Raiya Road Rajkot',
            'latitude' => '22.298340',
            'longitude' => '70.786873',
            'job'=>'admin',
            'bio'=> 'I am admin',
            'company' =>'Guru',
            'gender' => 1,
            'age' => 9,
            'height'=> 20,
	        'education'=> 3,
            'industry'=> 10,
            'salary'=> 9,
            'body_type'=> 9,
            'children'=> 1,
            'email_verified'=> 1,
            'phone_verified'=> 1,
            'otp_verified'=> 1,
            'faith'=> '3,9',
            'ethnticity'=> 1,
            'hobbies'=> 2,
            'password' => bcrypt('Konnected123**&'),
            'user_type' =>'admin',
        ]);
    }
}
