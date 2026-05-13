<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EmpresaExterna;

/**
 * EmpresaExternaSeeder
 * 
 * EXPLICACIÓN GENERAL:
 * Un Seeder carga datos de prueba en la base de datos
 * Se ejecuta con:
 * - php artisan db:seed (ejecutar todos los seeders)
 * - php artisan migrate:fresh --seed (resetear BD y cargar seeders)
 * - php artisan db:seed --class=EmpresaExternaSeeder (ejecutar solo este seeder)
 * 
 * IMPORTANTE:
 * Este archivo NO requiere cambios importantes porque ya usa el Factory
 * que contiene el nuevo campo 'calificacion'
 */
class EmpresaExternaSeeder extends Seeder
{
    /**
     * run()
     * 
     * EXPLICACIÓN:
     * Crea 50 empresas falsas usando la Factory
     * EmpresaExterna::factory()->count(50)->create()
     * - factory(): Usa la EmpresaExternaFactory
     * - count(50): Crea 50 registros
     * - create(): Los inserta en la base de datos
     */
    public function run(): void
    {
        // ✅ Crea 50 empresas con datos aleatorios (incluyendo calificacion)
        EmpresaExterna::factory()->count(50)->create();
    }
}