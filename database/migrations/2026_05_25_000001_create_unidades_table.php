<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('unidades', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();
            $table->string('placa')->nullable();
            $table->string('marca')->nullable();
            $table->integer('capacidad')->default(4);
            $table->enum('estado', ['activa', 'inactiva'])->default('activa');
            $table->integer('personas_por_unidad')->default(1);
            $table->boolean('tiene_mascotas')->default(false);
            $table->integer('vehiculos')->default(0);
            $table->enum('tipo_ocupacion', ['Propietario', 'Inquilino'])->default('Propietario');
            $table->foreignId('residente_id')->nullable()->constrained('residentes')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unidades');
    }
};
