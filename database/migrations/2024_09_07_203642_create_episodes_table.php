<?php

use App\Models\Season;
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
        Schema::create('episodes', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Season::class);

            $table->unsignedBigInteger('external_id');
            $table->unsignedInteger('number')->nullable();
            $table->string('name')->nullable();
            $table->string('type');
            $table->date('premiered')->nullable();
            $table->dateTime('air_timestamp')->nullable();
            $table->unsignedInteger('runtime')->nullable();
            $table->string('image')->nullable();
            $table->text('summary')->nullable();

            $table->index('air_timestamp');
            $table->index('type');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('episodes');
    }
};
