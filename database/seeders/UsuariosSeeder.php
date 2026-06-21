<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Residente;
use App\Models\Cuota;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsuariosSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('12345678');

        // Usuario ADMINISTRADOR
        $admin = User::create([
            'name' => 'admin',
            'email' => 'admin@gmail.com',
            'activo' => 1,
            'email_verified_at' => now(),
            'password' => $password,
        ]);
        $admin->assignRole('Administrador');

        // Usuario DIRECTIVA
        $directiva = User::create([
            'name' => 'directiva',
            'email' => 'directiva@gmail.com',
            'activo' => 1,
            'email_verified_at' => now(),
            'password' => $password,
        ]);
        $directiva->assignRole('Miembro de Directiva');

        // Usuario RESIDENTE
        // RESIDENTE DEMO: se crea (o reutiliza) un registro real en la
        // tabla `residentes` y se vincula al usuario, para que los casos
        // de prueba que dependen de auth()->user()->residente_id (como
        // CU8 - Reserva-002, validación de morosidad) funcionen con datos
        // reales en vez de quedar huérfanos.
        $residenteDemo = Residente::firstOrCreate(
            ['email' => 'residente@gmail.com'],
            [
                'nombre' => 'Carlos',
                'apellido' => 'Mamani Quispe',
                'ci' => '9999001',
                'tipo_residente' => 'Propietario',
            ]
        );

        $residente = User::create([
            'name' => 'residente',
            'email' => 'residente@gmail.com',
            'activo' => 1,
            'email_verified_at' => now(),
            'password' => $password,
            'residente_id' => $residenteDemo->id,
        ]);
        $residente->assignRole('Residente');

        // Cuota vencida hace 15 días (3 bloques de mora = Bs. 150) para que
        // el caso de prueba Reserva-002 (CU8) tenga morosidad real lista
        // para probar sin necesidad de crear datos manuales por Tinker.
        Cuota::firstOrCreate(
            [
                'residente_id' => $residenteDemo->id,
                'titulo' => 'Cuota de prueba (morosidad demo)',
            ],
            [
                'descripcion' => 'Cuota generada automáticamente para probar CU8 - Reserva-002.',
                'monto' => 300,
                'fecha_emision' => now()->subDays(45)->toDateString(),
                'fecha_vencimiento' => now()->subDays(15)->toDateString(),
                'estado' => 'pendiente',
            ]
        );

        // Usuario PORTERO
        $control = User::create([
            'name' => 'portero',
            'email' => 'portero@gmail.com',
            'activo' => 1,
            'email_verified_at' => now(),
            'password' => $password,
        ]);
        $control->assignRole('Portero');
    }
}
