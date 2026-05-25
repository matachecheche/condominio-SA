<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('incidencias', function (Blueprint $table) {
            $table->id();
            $table->string('numero_seguimiento')->unique();
            $table->string('titulo');
            $table->text('descripcion');
            $table->enum('estado', ['pendiente', 'en_revision', 'resuelto', 'cerrado'])->default('pendiente');
            $table->enum('prioridad', ['baja', 'media', 'alta'])->default('media');
            $table->text('respuesta_admin')->nullable();
            $table->foreignId('residente_id')->constrained('residentes')->cascadeOnDelete();
            $table->foreignId('atendido_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('fecha_atencion')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incidencias');
    }
};
