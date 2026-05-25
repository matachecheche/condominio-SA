<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Unidad;
use App\Models\Residente;

class UnidadSeeder extends Seeder
{
    public function run(): void
    {
        $residentes = Residente::orderBy('id')->get();

        for ($i = 1; $i <= 53; $i++) {
            $residente = $residentes->get($i - 1);
            Unidad::create([
                'codigo'              => 'U-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'estado'              => 'activa',
                'tipo_ocupacion'      => $i % 3 === 0 ? 'Inquilino' : 'Propietario',
                'capacidad'           => 4,
                'personas_por_unidad' => rand(1, 4),
                'vehiculos'           => rand(0, 2),
                'tiene_mascotas'      => (bool) rand(0, 1),
                'residente_id'        => $residente?->id,
            ]);
        }
    }
}
