<?php

namespace Database\Seeders;

use App\Models\SeatClass;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SeatClassSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            SeatClass::create([
                'name' => 'Economy'
            ]);
            SeatClass::create([
                'name' => 'Premium Economy'
            ]);
            SeatClass::create([
                'name' => 'Business Class'
            ]);
            SeatClass::create([
                'name' => 'First Class'
            ]);
        });
    }
}
