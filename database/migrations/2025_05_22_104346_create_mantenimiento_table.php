<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('mantenimientos', function (Blueprint $table) {
            $table->id();
            $table->string('descripcion');
            $table->integer('estado');

            // 👇 ESTA ES LA CLAVE QUE TE FALTA
            $table->string('prioridad')->default('media');

            $table->dateTime('fecha_hora');
            $table->decimal('monto', 10, 2);

            $table->unsignedBigInteger('usuario_id');
            $table->foreign('usuario_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->unsignedBigInteger('empresaExterna_id')->nullable();
            $table->foreign('empresaExterna_id')
                ->references('id')
                ->on('empresa_externas')
                ->onDelete('set null');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mantenimientos');
    }
};