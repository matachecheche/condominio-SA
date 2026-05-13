<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * MIGRACIÓN: Crear tabla 'empresa_externas'
 * 
 * EXPLICACIÓN GENERAL:
 * Una migración define la estructura de las tablas de la base de datos.
 * Esta migración crea la tabla 'empresa_externas' con todos sus campos,
 * incluyendo el nuevo campo 'calificacion'.
 * Se ejecuta con: php artisan migrate
 */
return new class extends Migration {
    /**
     * up() - Crear tabla
     * Se ejecuta cuando haces: php artisan migrate
     * Aquí defines la estructura de la tabla y sus columnas
     */
    public function up(): void {
        Schema::create('empresa_externas', function (Blueprint $table) {
            // COLUMNAS PRINCIPALES
            $table->id(); // ID único autoincremental (PRIMARY KEY)
            
            // INFORMACIÓN DE LA EMPRESA
            $table->string('nombre'); // Nombre de la empresa (Ej: "Limpieza Total S.A.")
            $table->string('servicio'); // Tipo de servicio que ofrece (Ej: "Limpieza", "Electricidad")
            $table->string('telefono')->nullable(); // Número de teléfono (opcional)
            $table->string('correo')->nullable(); // Correo electrónico (opcional)
            $table->string('direccion')->nullable(); // Dirección física (opcional)
            
            // ✅ NUEVO CAMPO: CALIFICACIÓN
            // EXPLICACIÓN: Almacena la calificación de 1 a 5 estrellas
            $table->unsignedTinyInteger('calificacion')->default(3); // Valores: 1-5, por defecto 3
            
            // OTROS CAMPOS
            $table->text('observacion')->nullable(); // Notas u observaciones sobre la empresa (opcional)
            
            // TIMESTAMPS AUTOMÁTICOS
            $table->timestamps(); // Crea 'created_at' y 'updated_at'
        });
    }

    /**
     * down() - Eliminar tabla
     * Se ejecuta cuando haces: php artisan migrate:rollback
     * Deshace los cambios de la migración (elimina la tabla)
     */
    public function down(): void {
        Schema::dropIfExists('empresa_externas');
    }
};