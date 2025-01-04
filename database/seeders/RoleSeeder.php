<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            Role::create([
                'name' => 'base_user'
            ]);
            Role::create([
                'name' => 'airline_operator'
            ]);
            Role::create([
                'name' => 'admin'
            ]);
            Role::create([
                'name' => 'super_user'
            ]);
        });
    }
}
