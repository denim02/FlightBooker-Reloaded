<?php

use App\Models\Airline;
use App\Models\Airport;
use App\Models\RouteFrequency;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('routes', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->dateTime('departure_time');
            $table->dateTime('arrival_time');
            $table->boolean('is_repeating')->default(false);
            $table->enum('frequency', ['DAILY', 'WEEKLY', 'MONTHLY', 'YEARLY'])->nullable();
            $table->foreignIdFor(Airline::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Airport::class, 'departure_airport_code')->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Airport::class, 'arrival_airport_code')->constrained()->cascadeOnDelete();
            $table->integer('route_group_id')->unsigned();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('routes');
    }
};
