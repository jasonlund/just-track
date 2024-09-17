<?php

use App\Models\Show;
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
        Schema::create('seasons', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Show::class);

            $table->unsignedBigInteger('external_id');
            $table->unsignedInteger('number');
            $table->date('air_date')->nullable();
            $table->string('name')->nullable();
            $table->text('overview')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seasons');
    }
};
