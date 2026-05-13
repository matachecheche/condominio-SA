<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * EXPLICACIÓN: Este archivo define la estructura de la tabla 'comunicados' en la base de datos.
     * Se agregó el campo 'destinatarios' para especificar a quién va dirigido el comunicado.
     */
    public function up(): void
    {
        Schema::create('comunicados', function (Blueprint $table) {
            $table->id(); // ID único autoincremental
            $table->string('titulo'); // Título del comunicado
            $table->text('contenido'); // Contenido del comunicado
            $table->enum('tipo', ['Urgente', 'Informativo'])->default('Informativo'); // Tipo de comunicado
            
            // ✅ NUEVO CAMPO: destinatarios
            // Define a quién va dirigido: Todos, Residentes, o Empleados
            $table->enum('destinatarios', ['Todos', 'Residentes', 'Empleados'])->default('Todos');
            
            $table->timestamp('fecha_publicacion')->useCurrent(); // Cuándo se publica
            $table->boolean('notificado')->default(false); // Si fue notificado
            $table->unsignedBigInteger('usuario_id'); // ID del usuario que crea el comunicado
            $table->timestamps(); // created_at y updated_at

            // Relación con tabla users
            $table->foreign('usuario_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     * Se ejecuta si necesitas deshacer la migración.
     */
    public function down(): void
    {
        Schema::dropIfExists('comunicados');
    }
};