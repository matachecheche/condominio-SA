<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Evento;
use App\Models\User;

/**
 * Seeder de Eventos Comunitarios — CU19 — Condominio San Diego
 */
class EventoSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = User::whereHas('roles', fn($q) => $q->where('name', 'Administrador'))
                       ->value('id') ?? 1;

        $eventos = [
            [
                'nombre' => 'Asamblea General Ordinaria',
                'descripcion' => 'Reunión anual para revisar el informe financiero y elegir a los nuevos representantes del comité de convivencia.',
                'lugar' => 'Salón de Eventos - Bloque A',
                'fecha_hora' => now()->addDays(10)->setTime(18, 30),
                'cupo_maximo' => 80,
                'estado' => 'programado',
                'organizador_id' => $adminId,
            ],
            [
                'nombre' => 'Bingo Familiar de Integración',
                'descripcion' => 'Actividad recreativa para residentes y sus familias, con premios donados por comercios aliados del condominio.',
                'lugar' => 'Área de Piscina',
                'fecha_hora' => now()->addDays(17)->setTime(16, 0),
                'cupo_maximo' => 50,
                'estado' => 'programado',
                'organizador_id' => $adminId,
            ],
            [
                'nombre' => 'Jornada de Reciclaje y Cuidado Ambiental',
                'descripcion' => 'Capacitación sobre separación de residuos y entrega de contenedores de reciclaje para cada unidad habitacional.',
                'lugar' => 'Parqueo Común - Bloque B',
                'fecha_hora' => now()->subDays(5)->setTime(9, 0),
                'cupo_maximo' => null,
                'estado' => 'finalizado',
                'organizador_id' => $adminId,
            ],
            [
                'nombre' => 'Torneo de Fútbol Vecinal',
                'descripcion' => 'Torneo relámpago entre equipos de residentes por bloque, con premiación al finalizar la jornada.',
                'lugar' => 'Cancha Polifuncional',
                'fecha_hora' => now()->addDays(3)->setTime(15, 0),
                'cupo_maximo' => 60,
                'estado' => 'cancelado',
                'organizador_id' => $adminId,
            ],
            [
                'nombre' => 'Celebración Fiestas Patrias del Condominio',
                'descripcion' => 'Encuentro comunitario con música, comida típica y actividades para niños en conmemoración de las fiestas patrias.',
                'lugar' => 'Salón de Eventos - Bloque A',
                'fecha_hora' => now()->addDays(25)->setTime(19, 0),
                'cupo_maximo' => 100,
                'estado' => 'programado',
                'organizador_id' => $adminId,
            ],
        ];

        foreach ($eventos as $data) {
            Evento::create($data);
        }

        $this->command->info('✔ ' . count($eventos) . ' eventos comunitarios generados.');
    }
}
