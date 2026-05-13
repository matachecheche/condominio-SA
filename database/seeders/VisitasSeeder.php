<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Visita;

/**
 * VisitasSeeder
 * 
 * EXPLICACIÓN GENERAL:
 * Un Seeder carga datos de prueba en la base de datos
 * Se ejecuta con:
 * - php artisan db:seed (ejecutar todos los seeders)
 * - php artisan migrate:fresh --seed (resetear BD y cargar seeders)
 * - php artisan db:seed --class=VisitasSeeder (ejecutar solo este seeder)
 * 
 * IMPORTANTE:
 * Este archivo NO requiere cambios importantes porque ya usa el Factory
 * que contiene el nuevo campo 'acompanante'
 */
class VisitasSeeder extends Seeder
{
    /**
     * run()
     * 
     * EXPLICACIÓN:
     * Crea visitas con diferentes estados usando la Factory
     * El Factory ahora genera automáticamente el campo 'acompanante'
     */
    public function run(): void
    {
        // Crear visitas con diferentes estados
        // El campo 'acompanante' se genera automáticamente según la Factory
        Visita::factory(5)->pendiente()->create();
        Visita::factory(3)->enCurso()->create();
        Visita::factory(10)->finalizada()->create();
        Visita::factory(2)->rechazada()->create();
        
        // Crear algunas visitas aleatorias
        Visita::factory(5)->create();
    }
}