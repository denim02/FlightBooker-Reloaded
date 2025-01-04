<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Route;
use App\Models\Airline;
use App\Models\Airport;
use App\Models\Airplane;
use Illuminate\Support\Facades\DB;

class RouteService
{
    public function __construct()
    {
    }

    public function createRoute(array $routeData)
    {
        return DB::transaction(function () use ($routeData) {
            $this->validateRouteData($routeData);
            $routes = [];

            if (!$routeData['is_repeating']) {
                $route = $this->createSingleRoute($routeData);
                $routes[] = $route;
            } else {
                $routes = $this->createRepeatingRoutes($routeData);
            }

            return $routes;
        });
    }

    protected function createSingleRoute(array $routeData): Route
    {
        // Get the last route group ID or default to 1
        $lastRoute = Route::orderBy('route_group_id', 'desc')->first();
        $routeGroupId = $lastRoute ? $lastRoute->route_group_id + 1 : 1;

        // Create the route
        $route = Route::create([
            'airline_id' => $routeData['airline_id'],
            'departure_airport_code' => $routeData['flights'][0]['departure_airport_code'],
            'arrival_airport_code' => end($routeData['flights'])['arrival_airport_code'],
            'departure_time' => Carbon::parse($routeData['flights'][0]['departure_time']),
            'arrival_time' => Carbon::parse(end($routeData['flights'])['arrival_time']),
            'is_repeating' => $routeData['is_repeating'],
            'route_group_id' => $routeGroupId,
        ]);

        // Create flights for the route
        foreach ($routeData['flights'] as $flightData) {
            $airplane = Airplane::findOrFail($flightData['airplane_id']);

            $flight = $route->flights()->create([
                'airplane_id' => $flightData['airplane_id'],
                'departure_airport_code' => $flightData['departure_airport_code'],
                'arrival_airport_code' => $flightData['arrival_airport_code'],
                'departure_time' => Carbon::parse($flightData['departure_time']),
                'arrival_time' => Carbon::parse($flightData['arrival_time']),
            ]);
        }

        // Create route pricing for each seat class
        foreach ($routeData['prices'] as $seatClassId => $price) {
            $route->pricings()->attach($seatClassId, ['price_per_seat' => $price]);
        }

        return $route;
    }

    /**
     * Create repeating routes based on frequency
     */
    protected function createRepeatingRoutes(array $routeData): array
    {
        $routes = [];
        $originalDepartureTime = Carbon::parse($routeData['flights'][0]['departure_time']);
        $departureTime = $originalDepartureTime->copy();
        $travelTime = Carbon::parse(end($routeData['flights'])['arrival_time'])
            ->diffInSeconds($originalDepartureTime);

        $lastRoute = Route::orderBy('route_group_id', 'desc')->first();
        $routeGroupId = $lastRoute ? $lastRoute->route_group_id + 1 : 1;

        // Parse repeat_until date or default to 1 year
        $repeatUntil = isset($routeData['repeat_until'])
            ? Carbon::parse($routeData['repeat_until'])
            : $originalDepartureTime->copy()->addYear();

        // Change condition to use repeat_until
        while ($departureTime <= $repeatUntil) {
            // Create route
            $route = Route::create([
                'airline_id' => $routeData['airline_id'],
                'departure_airport_code' => $routeData['flights'][0]['departure_airport_code'],
                'arrival_airport_code' => end($routeData['flights'])['arrival_airport_code'],
                'departure_time' => $departureTime,
                'arrival_time' => $departureTime->copy()->addSeconds($travelTime),
                'is_repeating' => true,
                'frequency' => strtoupper($routeData['frequency']),
                'route_group_id' => $routeGroupId,
            ]);

            // Create flights for this route instance
            foreach ($routeData['flights'] as $index => $flightData) {
                $airplane = Airplane::findOrFail($flightData['airplane_id']);

                if ($index > 0) {
                    // For connecting flights, calculate the time difference from the original schedule
                    $timeFromFirst = Carbon::parse($flightData['departure_time'])
                        ->diffInSeconds($originalDepartureTime);
                    $flightDuration = Carbon::parse($flightData['arrival_time'])
                        ->diffInSeconds(Carbon::parse($flightData['departure_time']));

                    $flightDepartureTime = $departureTime->copy()->addSeconds($timeFromFirst);
                    $flightArrivalTime = $flightDepartureTime->copy()->addSeconds($flightDuration);
                } else {
                    // For first flight in sequence
                    $flightDuration = Carbon::parse($flightData['arrival_time'])
                        ->diffInSeconds(Carbon::parse($flightData['departure_time']));
                    $flightDepartureTime = $departureTime->copy();
                    $flightArrivalTime = $flightDepartureTime->copy()->addSeconds($flightDuration);
                }

                $flight = $route->flights()->create([
                    'airplane_id' => $flightData['airplane_id'],
                    'departure_airport_code' => $flightData['departure_airport_code'],
                    'arrival_airport_code' => $flightData['arrival_airport_code'],
                    'departure_time' => $flightDepartureTime,
                    'arrival_time' => $flightArrivalTime,
                ]);
            }

            // Create route pricing for each seat class
            foreach ($routeData['prices'] as $seatClassId => $price) {
                $route->pricings()->attach($seatClassId, ['price_per_seat' => $price]);
            }

            $routes[] = $route;

            // Increment departure time based on frequency
            switch ($routeData['frequency']) {
                case 'daily':
                    $departureTime->addDay();
                    break;
                case 'weekly':
                    $departureTime->addWeek();
                    break;
                case 'monthly':
                    $departureTime->addMonth();
                    break;
                case 'yearly':
                    $departureTime->addYear();
                    break;
            }

            // Break if next iteration would exceed repeat_until
            if ($departureTime > $repeatUntil) {
                break;
            }
        }

        return $routes;
    }

    protected function validateRouteData(array $routeData): void
    {
        // Validate frequency
        if (
            $routeData['is_repeating'] && !in_array(
                strtolower($routeData['frequency']),
                ['daily', 'weekly', 'monthly', 'yearly']
            )
        ) {
            throw new \Exception('Frequency must be either daily, monthly, weekly, or yearly.');
        }

        // Validate transit routes
        $flightCount = count($routeData['flights']);
        if (!$routeData['is_transit'] && $flightCount > 1) {
            throw new \Exception('Direct routes can only have one flight.');
        }
        if ($routeData['is_transit'] && $flightCount < 2) {
            throw new \Exception('Transit routes must have at least two flights.');
        }

        // Validate airline exists
        if (!Airline::find($routeData['airline_id'])) {
            throw new \Exception("No airline with id {$routeData['airline_id']} exists.");
        }

        // Validate airports and flight sequence
        foreach ($routeData['flights'] as $index => $flight) {
            // Validate airplane exists
            if (!Airplane::find($flight['airplane_id'])) {
                throw new \Exception("No airplane with id {$flight['airplane_id']} exists.");
            }

            // Validate airports exist
            if (!Airport::find($flight['departure_airport_code'])) {
                throw new \Exception("No airport with code {$flight['departure_airport_code']} exists.");
            }
            if (!Airport::find($flight['arrival_airport_code'])) {
                throw new \Exception("No airport with code {$flight['arrival_airport_code']} exists.");
            }

            // Validate departure is before arrival
            $departureTime = Carbon::parse($flight['departure_time']);
            $arrivalTime = Carbon::parse($flight['arrival_time']);
            if ($departureTime >= $arrivalTime) {
                throw new \Exception('Departure time must be before arrival time.');
            }

            // Validate connecting flights
            if ($index > 0) {
                $prevFlight = $routeData['flights'][$index - 1];
                if ($prevFlight['arrival_airport_code'] !== $flight['departure_airport_code']) {
                    throw new \Exception('The departure airport of a connecting flight must match the arrival airport of the previous flight.');
                }

                $prevArrivalTime = Carbon::parse($prevFlight['arrival_time']);
                if ($departureTime <= $prevArrivalTime) {
                    throw new \Exception('Flight times must be in chronological order.');
                }
            }
        }
    }
}
