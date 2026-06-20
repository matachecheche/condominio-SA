<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Notificacion;
use App\Models\Residente;
use App\Models\User;

/**
 * Seeder de Notificaciones a Residentes — CU17 — Condominio San Diego
 * (Nombrado "CicloSeeder" para no chocar con un eventual seeder previo de Notificacion).
 */
class NotificacionCicloSeeder extends Seeder
{
    public function run(): void
    {
        if (Residente::count() === 0) {
            $this->command->warn('No hay residentes. Ejecutá ResidentesSeeder primero.');
            return;
        }

        $adminId = User::whereHas('roles', fn($q) => $q->where('name', 'Administrador'))
                       ->value('id') ?? 1;

        $residentes = Residente::pluck('id')->toArray();

        $plantillas = [
            ['titulo' => 'Corte de agua programado', 'contenido' => 'Se informa que el día de mañana se realizará un corte de agua de 08:00 a 14:00 por mantenimiento de cisternas.', 'tipo' => 'Urgente'],
            ['titulo' => 'Recordatorio: vencimiento de cuota mensual', 'contenido' => 'Le recordamos que el plazo para el pago de la cuota de mantenimiento vence este viernes.', 'tipo' => 'Recordatorio'],
            ['titulo' => 'Nuevo horario de atención en portería', 'contenido' => 'A partir del próximo lunes, portería atenderá de 06:00 a 22:00 horas de manera continua.', 'tipo' => 'Informativa'],
        ];

        $total = 0;
        foreach ($residentes as $residenteId) {
            foreach ($plantillas as $i => $p) {
                Notificacion::create([
                    'titulo' => $p['titulo'],
                    'contenido' => $p['contenido'],
                    'tipo' => $p['tipo'],
                    'fecha_hora' => now()->subDays(3 - $i),
                    'residente_id' => $residenteId,
                    'enviada_por' => $adminId,
                    'leida' => $i === 0,
                ]);
                $total++;
            }
        }

        $this->command->info("✔ {$total} notificaciones generadas para " . count($residentes) . ' residentes.');
    }
}
