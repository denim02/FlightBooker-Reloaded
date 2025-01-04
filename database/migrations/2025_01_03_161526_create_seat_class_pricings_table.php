<?php

use App\Models\Route;
use App\Models\SeatClass;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('seat_class_pricings', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(SeatClass::class, 'seat_class_id')->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Route::class, 'route_id')->constrained()->cascadeOnDelete();
            $table->double('price_per_seat')->unsigned();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seat_class_pricings');
    }
};
