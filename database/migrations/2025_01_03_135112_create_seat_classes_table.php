<?php

use App\Models\SeatClassOptions;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('seat_classes', function (Blueprint $table) {
            $table->id();
            $table->enum('name', ['Economy', 'Premium Economy', 'Business Class', 'First Class']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seat_classes');
    }
};
