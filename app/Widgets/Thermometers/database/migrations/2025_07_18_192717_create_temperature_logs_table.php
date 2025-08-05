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
        Schema::create('temperature_logs', function (Blueprint $table) {
            $table->id();
            $table->string('device_id', 100)->index();
            $table->bigInteger('timestamp'); // Timestamp Tuya en millisecondes
            $table->string('code', 50); // va_temperature, va_humidity, etc.
            $table->string('value', 20); // Valeur brute (285 pour 28.5°C)
            $table->json('raw_data')->nullable(); // Données brutes complètes pour debug
            $table->timestamps();
            
            // Index composite pour les requêtes fréquentes
            $table->index(['device_id', 'timestamp']);
            $table->index(['device_id', 'code', 'timestamp']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('temperature_logs');
    }
};
