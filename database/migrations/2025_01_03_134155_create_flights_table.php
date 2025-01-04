<?php

use App\Models\Airplane;
use App\Models\Airport;
use App\Models\Route;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('flights', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->dateTime('departure_time');
            $table->dateTime('arrival_time');
            $table->double('delay')->nullable();
            $table->foreignIdFor(Airport::class, 'departure_airport_code')->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Airport::class, 'arrival_airport_code')->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Airplane::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Route::class)->constrained()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flights');
    }
};
