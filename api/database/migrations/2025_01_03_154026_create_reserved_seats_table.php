<?php

use App\Models\Flight;
use App\Models\Reservation;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reserved_seats', function (Blueprint $table) {
            $table->id();
            $table->integer('row_no');
            $table->integer('col_no');
            $table->foreignIdFor(Flight::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Reservation::class)->constrained()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reserved_seats');
    }
};
