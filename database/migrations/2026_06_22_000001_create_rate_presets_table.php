<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rate_presets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('rate_per_1000', 10, 2);
            $table->unsignedInteger('term_days');
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rate_presets');
    }
};
