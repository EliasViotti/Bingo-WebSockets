<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lineas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tarjeta_id')->constrained('tarjetas')->onDelete('cascade');
            $table->tinyInteger('numero_linea')->unsigned();
            $table->tinyInteger('n1')->unsigned();
            $table->tinyInteger('n2')->unsigned();
            $table->tinyInteger('n3')->unsigned();
            $table->tinyInteger('n4')->unsigned();
            $table->tinyInteger('n5')->unsigned();
            $table->tinyInteger('n6')->unsigned();
            $table->tinyInteger('n7')->unsigned();
            $table->tinyInteger('n8')->unsigned();
            $table->tinyInteger('n9')->unsigned();
            $table->tinyInteger('n10')->unsigned();

            // Índice único compuesto
            $table->unique(['tarjeta_id', 'numero_linea']);
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('lineas');
    }
};
