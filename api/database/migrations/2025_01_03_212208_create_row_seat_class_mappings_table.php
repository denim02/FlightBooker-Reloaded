<?php

use App\Models\Airplane;
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
        Schema::create('row_seat_class_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Airplane::class);
            $table->foreignIdFor(SeatClass::class);
            $table->json('rows');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('row_seat_class_mappings');
    }
};
