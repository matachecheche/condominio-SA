<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Comunicado;

/**
 * ComunicadoSeeder
 * 
 * EXPLICACIÓN: Este Seeder carga datos de prueba en la base de datos
 * Se ejecuta con: php artisan db:seed o php artisan migrate:fresh --seed
 * 
 * IMPORTANTE: No requiere cambios porque ya usa el Factory
 * que contiene el nuevo campo 'destinatarios'
 */
class ComunicadoSeeder extends Seeder
{
    /**
     * run()
     * 
     * EXPLICACIÓN: Crea 10 comunicados falsos usando la Factory
     * Comunicado::factory()->count(10)->create()
     * - factory(): Usa la ComunicadoFactory
     * - count(10): Crea 10 registros
     * - create(): Los inserta en la base de datos
     */
    public function run(): void
    {
        // ✅ Crea 10 comunicados con datos aleatorios (incluyendo destinatarios)
        Comunicado::factory()->count(10)->create();
    }
}