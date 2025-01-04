<?php

namespace Database\Seeders;

use App\Models\Airline;
use App\Models\Airplane;
use App\Models\Airport;
use App\Models\Inquiry;
use App\Models\User;
use App\Services\RouteService;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StaticSeeder extends Seeder
{
    protected $routeService;

    public function __construct(RouteService $routeService)
    {
        $this->routeService = $routeService;
    }

    public function run(): void
    {
        DB::transaction(function () {
            // Seed airports
            Airport::upsert([
                [
                    'code' => 'SOF',
                    'name' => 'Sofia Airport',
                    'city' => 'Sofia',
                    'country' => 'Bulgaria'
                ],
                [
                    'code' => 'VIE',
                    'name' => 'Vienna International Airport',
                    'city' => 'Vienna',
                    'country' => 'Austria'
                ],
                [
                    'code' => 'IST',
                    'name' => 'Istanbul International Airport',
                    'city' => 'Istanbul',
                    'country' => 'Turkey'
                ],
                [
                    'code' => 'TIA',
                    'name' => 'Tirana International Airport',
                    'city' => 'Tirana',
                    'country' => 'Albania'
                ],
                [
                    'code' => 'BER',
                    'name' => 'Berlin International Airport',
                    'city' => 'Berlin',
                    'country' => 'Germany'
                ]
            ], uniqueBy: ['code', 'name']);

            // Seed airplanes
            $planes = [
                ['brand' => 'Boeing', 'model' => '737', 'nr_cols' => 6, 'nr_rows' => 30],
                ['brand' => 'Airbus', 'model' => 'A320', 'nr_cols' => 4, 'nr_rows' => 30],
                ['brand' => 'Boeing', 'model' => '747', 'nr_cols' => 7, 'nr_rows' => 30],
            ];
            foreach ($planes as $plane) {
                $createdPlane = Airplane::create($plane);
                $createdPlane->rowMappings()->sync([
                    1 => ['rows' => [1, 2, 3]],
                    2 => ['rows' => [4, 5, 6]],
                    3 => ['rows' => range(7, 15)],
                    4 => ['rows' => range(16, 30)],
                ]);
            }

            // Seed airlines
            $lufthansa = Airline::create(
                ['name' => 'Lufthansa', 'country' => 'Austria', 'email' => 'lufthansa@gmail.com', 'phone_number' => '+359872351413'],
            );
            Airline::upsert([
                ['name' => 'Bulgaria Air', 'country' => 'Bulgaria', 'email' => 'bulgariaair@gmail.com', 'phone_number' => '+359872351413'],
                ['name' => 'Turkish Airlines', 'country' => 'Turkey', 'email' => 'turkishairlines@gmail.com', 'phone_number' => '+359872351413'],
            ], uniqueBy: ['name']);
            User::whereHas('roles', function (Builder $query) {
                $query->where('name', '=', 'airline_operator');
            })->first()->airline = $lufthansa;

            // Seed routes using RouteService
            $routes = [
                [
                    'airline_id' => 2,
                    'is_transit' => true,
                    'is_repeating' => true,
                    'frequency' => 'monthly',
                    'repeat_until' => '2025-12-31',
                    'flights' => [
                        [
                            'departure_airport_code' => 'TIA',
                            'arrival_airport_code' => 'VIE',
                            'departure_time' => '2025-04-10 20:30:00',
                            'arrival_time' => '2025-04-10 22:30:00',
                            'airplane_id' => 1,
                        ],
                        [
                            'departure_airport_code' => 'VIE',
                            'arrival_airport_code' => 'SOF',
                            'departure_time' => '2025-04-11 00:00:00',
                            'arrival_time' => '2025-04-11 03:30:00',
                            'airplane_id' => 1,
                        ],
                        [
                            'departure_airport_code' => 'SOF',
                            'arrival_airport_code' => 'TIA',
                            'departure_time' => '2025-04-11 04:00:00',
                            'arrival_time' => '2025-04-11 05:30:00',
                            'airplane_id' => 2,
                        ],
                    ],
                    'prices' => [
                        1 => 400,
                        4 => 100,
                        3 => 200,
                        2 => 300,
                    ],
                ],
                [
                    'airline_id' => 1,
                    'is_transit' => false,
                    'is_repeating' => false,
                    'flights' => [
                        [
                            'departure_airport_code' => 'SOF',
                            'arrival_airport_code' => 'IST',
                            'departure_time' => '2025-04-12 08:00:00',
                            'arrival_time' => '2025-04-12 10:30:00',
                            'airplane_id' => 1,
                        ],
                    ],
                    'prices' => [
                        1 => 350,
                        4 => 120,
                        3 => 200,
                        2 => 280,
                    ],
                ],
                [
                    'airline_id' => 2,
                    'is_transit' => true,
                    'is_repeating' => true,
                    'frequency' => 'weekly',
                    'repeat_until' => '2025-09-30',
                    'flights' => [
                        [
                            'departure_airport_code' => 'TIA',
                            'arrival_airport_code' => 'BER',
                            'departure_time' => '2025-04-13 10:00:00',
                            'arrival_time' => '2025-04-13 18:30:00',
                            'airplane_id' => 2,
                        ],
                        [
                            'departure_airport_code' => 'BER',
                            'arrival_airport_code' => 'VIE',
                            'departure_time' => '2025-04-13 20:00:00',
                            'arrival_time' => '2025-04-13 22:30:00',
                            'airplane_id' => 2,
                        ],
                    ],
                    'prices' => [
                        1 => 450,
                        4 => 150,
                        3 => 280,
                        2 => 380,
                    ],
                ],
            ];

            foreach ($routes as $routeData) {
                $this->routeService->createRoute($routeData);
            }

            // Seed complaints
            Inquiry::create([
                'subject' => 'Lorem ipsum',
                'description' => 'This is a sample complaint.',
                'complainant_id' => User::first()->id,
            ]);
        });
    }
}
