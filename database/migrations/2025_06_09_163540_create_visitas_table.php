<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * MIGRACIÓN: Crear tabla 'visitas'
 * 
 * EXPLICACIÓN GENERAL:
 * Una migración define la estructura de las tablas de la base de datos.
 * Esta migración crea la tabla 'visitas' con todos sus campos,
 * incluyendo el nuevo campo 'acompanante'.
 * Se ejecuta con: php artisan migrate
 */
return new class extends Migration
{
    /**
     * up() - Crear tabla
     * Se ejecuta cuando haces: php artisan migrate
     * Aquí defines la estructura de la tabla y sus columnas
     */
    public function up(): void
    {
        Schema::create('visitas', function (Blueprint $table) {
            // COLUMNAS PRINCIPALES
            $table->id(); // ID único autoincremental (PRIMARY KEY)
            
            // RELACIÓN CON RESIDENTE
            $table->foreignId('residente_id')->constrained('residentes')->onDelete('cascade');
            
            // INFORMACIÓN DEL VISITANTE
            $table->string('nombre_visitante'); // Nombre completo del visitante
            $table->string('ci_visitante', 20); // Cédula/ID del visitante
            $table->string('placa_vehiculo', 20)->nullable(); // Placa del vehículo (opcional)
            $table->string('motivo'); // Motivo de la visita (Ej: Visita familiar, Servicio técnico)
            
            // ✅ NUEVO CAMPO: ACOMPAÑANTE
            // EXPLICACIÓN: Indica si el visitante va acompañado o no
            $table->boolean('acompanante')->default(false); // false = sin acompañante, true = con acompañante
            
            // FECHAS Y HORARIOS
            $table->dateTime('fecha_inicio'); // Cuándo comienza la visita autorizada
            $table->dateTime('fecha_fin'); // Cuándo termina la visita autorizada
            $table->dateTime('hora_entrada')->nullable(); // Cuándo ingresó (registrado en portería)
            $table->dateTime('hora_salida')->nullable(); // Cuándo salió (registrado en portería)
            
            // IDENTIFICADOR Y ESTADO
            $table->string('codigo', 6)->unique(); // Código único de 6 dígitos para validación
            $table->enum('estado', ['pendiente', 'en_curso', 'finalizada', 'rechazada'])->default('pendiente');
            
            // USUARIOS QUE REGISTRAN ENTRADA/SALIDA
            $table->foreignId('user_entrada_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('user_salida_id')->nullable()->constrained('users')->onDelete('set null');
            
            // NOTAS
            $table->text('observaciones')->nullable(); // Observaciones sobre la visita
            
            // TIMESTAMPS AUTOMÁTICOS
            $table->timestamps(); // Crea 'created_at' y 'updated_at'

            // ÍNDICES PARA BÚSQUEDAS FRECUENTES
            $table->index(['codigo', 'ci_visitante']); // Para búsquedas rápidas por código + CI
            $table->index('estado'); // Para filtrar por estado
            $table->index(['fecha_inicio', 'fecha_fin']); // Para búsquedas por rango de fechas
        });
    }

    /**
     * down() - Eliminar tabla
     * Se ejecuta cuando haces: php artisan migrate:rollback
     * Deshace los cambios de la migración (elimina la tabla)
     */
    public function down(): void
    {
        Schema::dropIfExists('visitas');
    }
};