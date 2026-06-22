<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * La columna "accion" de bitacoras era varchar(100), demasiado corta para
 * los mensajes descriptivos que arma VisitaController (nombre del
 * visitante, motivo, minutos de tardanza, etc.), lo que causaba un error
 * 500 ("value too long for type character varying(100)") al registrar
 * salidas, entradas, validaciones, etc. Se amplía a 255, suficiente para
 * el patrón de mensajes actual sin necesidad de truncar manualmente.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bitacoras', function (Blueprint $table) {
            $table->string('accion', 255)->change();
        });
    }

    public function down(): void
    {
        Schema::table('bitacoras', function (Blueprint $table) {
            $table->string('accion', 100)->change();
        });
    }
};
