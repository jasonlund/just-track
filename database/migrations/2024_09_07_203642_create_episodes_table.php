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

            $table->foreignIdFor(Show::class);
            $table->foreignIdFor(Season::class);

            $table->unsignedBigInteger('external_id');
            $table->unsignedInteger('number');
            $table->string('production_code')->nullable();
            $table->string('name')->nullable();
            $table->date('air_date')->nullable();
            $table->unsignedInteger('runtime')->nullable();
            $table->text('overview')->nullable();

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
