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
            $table->string('name');
            $table->string('type')->nullable();
            $table->string('language')->nullable();
            $table->string('status')->nullable();
            $table->unsignedInteger('runtime')->nullable();
            $table->unsignedInteger('average_runtime')->nullable();
            $table->date('premiered')->nullable();
            $table->date('ended')->nullable();
            $table->unsignedBigInteger('tvdb_id')->nullable();
            $table->unsignedBigInteger('imdb_id')->nullable();
            $table->string('image')->nullable();
            $table->text('summary')->nullable();
            $table->dateTime('external_updated_at');

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
