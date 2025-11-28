<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('juegos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->unique()->nullable();
            $table->enum('estado', ['esperando', 'jugando', 'finalizado'])->default('esperando')->nullable();
            $table->json('numeros_sorteados')->nullable();
            $table->foreignId('tarjeta_ganadora_id')->nullable()->constrained('tarjetas')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('juegos');
    }
};