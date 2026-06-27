<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('themes', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('view_path')->default('themes.default');
            $table->json('config')->nullable();
            $table->boolean('is_default')->default(false)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('theme_palettes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('theme_id')->constrained()->cascadeOnDelete();
            $table->string('key');
            $table->string('name');
            $table->json('colors');
            $table->boolean('is_default')->default(false)->index();
            $table->timestamps();

            $table->unique(['theme_id', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('theme_palettes');
        Schema::dropIfExists('themes');
    }
};
