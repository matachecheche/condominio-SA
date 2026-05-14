<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('propiedades', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();
            $table->string('tipo');
            $table->text('descripcion')->nullable();
            $table->string('ubicacion');
            $table->enum('estado', ['disponible', 'ocupada', 'activa', 'mantenimiento'])->default('disponible');
            
            $table->foreignId('residente_id')
                ->nullable()
                ->constrained('residentes')
                ->onUpdate('cascade')
                ->onDelete('set null');

            $table->timestamps();

            $table->index('codigo');
            $table->index('ubicacion');
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('propiedades');
    }
};
