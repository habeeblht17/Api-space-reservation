<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignUuid('space_id')->constrained('spaces')->onDelete('cascade');;
            $table->foreignUuid('plan_id')->constrained('plans')->onDelete('cascade');;
            $table->unsignedInteger('interval_count')->default(1); // The number of intervals on the plan table (e.g., 1 (i.e 1 Month), 2, 3)
            $table->dateTime('start_time')->nullable();
            $table->dateTime('end_time')->nullable();
            $table->dateTime('canceled_at')->nullable();

            
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
