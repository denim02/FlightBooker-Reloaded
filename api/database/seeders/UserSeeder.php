<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $baseUser = User::create([
                'name' => 'Deni',
                'email' => 'denimastori02@gmail.com',
                'password' => bcrypt('secret123'),
                'phone_number' => '+355682361513'
            ]);
            $baseUser->assignRole('base_user');

            $airlineOperator = User::create([
                'name' => 'Airline Op',
                'email' => 'airline@gmail.com',
                'password' => bcrypt('secret123')
            ]);
            $airlineOperator->assignRole('airline_operator');

            $admin = User::create([
                'name' => 'Admin',
                'email' => 'admin@gmail.com',
                'password' => bcrypt('secret123')
            ]);
            $admin->assignRole('admin');

            $suser = User::create([
                'name' => 'Super User',
                'email' => 'suser@gmail.com',
                'password' => bcrypt('secret123'),
            ]);
            $suser->assignRole('super_user');
        });

    }
}
