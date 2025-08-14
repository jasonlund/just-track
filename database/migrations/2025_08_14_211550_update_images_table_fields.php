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
        Schema::table('images', function (Blueprint $table) {
            $table->string('external_id')->after('imageable_id');
            $table->index('external_id');
            $table->dropColumn('internal_path');
            $table->renameColumn('external_path', 'path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('images', function (Blueprint $table) {
            $table->renameColumn('path', 'external_path');
            $table->string('internal_path')->nullable();
            $table->dropIndex(['external_id']);
            $table->dropColumn('external_id');
        });
    }
};
