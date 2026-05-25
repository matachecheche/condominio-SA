<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Incidencia;
use App\Models\Residente;

class IncidenciaSeeder extends Seeder
{
    public function run(): void
    {
        $residentes = Residente::pluck('id')->toArray();
        if (empty($residentes)) return;

        $ejemplos = [
            ['titulo' => 'Ruido excesivo en la noche', 'descripcion' => 'Vecinos del bloque B generan ruido hasta altas horas.', 'prioridad' => 'alta', 'estado' => 'pendiente'],
            ['titulo' => 'Fuga de agua en área común', 'descripcion' => 'Hay una fuga en la manguera del jardín principal.', 'prioridad' => 'alta', 'estado' => 'en_revision'],
            ['titulo' => 'Luz del pasillo apagada', 'descripcion' => 'El pasillo del 3er piso no tiene iluminación.', 'prioridad' => 'media', 'estado' => 'resuelto'],
            ['titulo' => 'Problemas con el ascensor', 'descripcion' => 'El ascensor hace ruidos extraños al subir.', 'prioridad' => 'alta', 'estado' => 'en_revision'],
            ['titulo' => 'Basura no recogida', 'descripcion' => 'Los contenedores del estacionamiento no se vaciaron esta semana.', 'prioridad' => 'baja', 'estado' => 'pendiente'],
        ];

        foreach ($ejemplos as $e) {
            Incidencia::create([
                'titulo'       => $e['titulo'],
                'descripcion'  => $e['descripcion'],
                'prioridad'    => $e['prioridad'],
                'estado'       => $e['estado'],
                'residente_id' => $residentes[array_rand($residentes)],
            ]);
        }
    }
}
