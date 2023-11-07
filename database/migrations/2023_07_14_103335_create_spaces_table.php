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
        Schema::create('spaces', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('category_id')->constrained('categories')->onDelete('cascade');
            $table->string('title')->unique();
            $table->tinyText('description');
            $table->decimal('rate_per_unit', 8, 2)->nullable();// Rate per unit measurement (i.e per square meter or per square feet)
            $table->tinyInteger('capacity');
            $table->string('measurement');
            $table->string('status')->default('active')->comment('pending, active');
            $table->boolean('availability')->default(true);
            $table->string('image')->nullable();
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spaces');
    }
};
