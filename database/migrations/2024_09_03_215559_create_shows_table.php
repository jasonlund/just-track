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
        Schema::create('shows', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('external_id');
            $table->string('name')->nullable();
            $table->string('original_name');
            $table->string('status')->nullable();
            $table->date('first_air_date')->nullable();
            $table->text('overview')->nullable();
            $table->char('origin_country', 2)->nullable();

            $table->timestamps();

            $table->unique('external_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shows');
    }
};
