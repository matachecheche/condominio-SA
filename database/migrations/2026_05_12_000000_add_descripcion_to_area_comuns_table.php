<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * EXPLICACIÓN DE LA MIGRACIÓN:
 * ────────────────────────────────────────────────────────────────────
 * Una migración es un archivo que modifica la estructura de la base de datos.
 * En este caso, estamos AGREGANDO una nueva columna llamada "descripcion"
 * a la tabla "area_comuns" que ya existe.
 * 
 * Métodos principales:
 * - up()    : Ejecuta los cambios cuando haces "php artisan migrate"
 * - down()  : Deshace los cambios cuando haces "php artisan migrate:rollback"
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * EXPLICACIÓN:
     * - Schema::table() = Modificar tabla existente (NO crear nueva)
     * - 'area_comuns' = Nombre de la tabla
     * - $table->text('descripcion') = Crear columna de tipo TEXT
     * - ->nullable() = Permite valores NULL (es decir, puede estar vacía)
     * - ->after('nombre') = Posiciona la columna DESPUÉS de "nombre"
     *                       (Esto es por estetica en la BD)
     */
    public function up(): void
    {
        Schema::table('area_comuns', function (Blueprint $table) {
            // Agregar columna descripcion de tipo texto, nullable, después de nombre
            $table->text('descripcion')->nullable()->after('nombre');
        });
    }

    /**
     * Reverse the migrations.
     * 
     * EXPLICACIÓN:
     * - Si ejecutas "php artisan migrate:rollback"
     * - Se ejecuta down() que ELIMINA la columna descripcion
     * - Es como un "deshacer" de los cambios
     * - Esto es IMPORTANTE para desarrollo y testing
     */
    public function down(): void
    {
        Schema::table('area_comuns', function (Blueprint $table) {
            // Eliminar la columna descripcion si se revierte la migración
            $table->dropColumn('descripcion');
        });
    }
};
