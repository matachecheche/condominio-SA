<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            \Database\Seeders\UnidadSeeder::class,
            \Database\Seeders\IncidenciaSeeder::class,
            PermissionSeeder::class,
            RolesSeeder::class,
            UsuariosSeeder::class,
            ClasificadoresSeeder::class,
            CargoEmpleadosSeeder::class,
            EmpleadosSeeder::class,
            ResidentesSeeder::class,
            VisitasSeeder::class,
            TipoCuotaSeeder::class,
            CuotaSeeder::class,
            PagoSeeder::class,
            EmpresaExternaSeeder::class,
            MantenimientoSeeder::class,
            AreaComunSeeder::class,
            ReservaSeeder::class,
            MultaSeeder::class,
            ComunicadoSeeder::class,
            ReclamoSeeder::class,
            EventoSeeder::class,
            NotificacionCicloSeeder::class,
        ]);
    }
}