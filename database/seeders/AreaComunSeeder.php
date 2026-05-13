<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AreaComun;

class AreaComunSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $areas = [
            [
                'nombre'      => 'Salón de Eventos',
                'monto'       => 250.00,
                'descripcion' => 'Salón equipado con mesas, sillas y proyector para eventos sociales.', // ← NUEVO
                'estado'      => 'activo',
            ],
            [
                'nombre'      => 'Piscina',
                'monto'       => 150.00,
                'descripcion' => 'Piscina olímpica con áreas de recreación infantil y adulto.', // ← NUEVO
                'estado'      => 'activo',
            ],
        ];

        foreach($areas as $area) {
            AreaComun::create([
                'nombre'      => $area['nombre'],
                'monto'       => $area['monto'],
                'descripcion' => $area['descripcion'], // ← NUEVO
                'estado'      => $area['estado'],
            ]);
        }
    }
}