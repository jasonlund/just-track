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
        Schema::create('images', function (Blueprint $table) {
            $table->id();

            // Polymorphic relationship fields
            $table->morphs('imageable');

            // Image metadata
            $table->string('type');
            $table->string('external_path');
            $table->string('internal_path')->nullable();
            $table->string('language')->nullable();
            $table->unsignedInteger('likes')->default(0);

            $table->timestamps();

            // Indexes for performance
            $table->index(['type', 'likes']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('images');
    }
};
