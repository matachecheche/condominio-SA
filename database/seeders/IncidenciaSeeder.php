<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Incidencia;
use App\Models\Residente;
use App\Models\User;

/**
 * Seeder de Denuncias e Incidencias — CU16 — Condominio San Diego
 *
 * Crea 40 incidencias ficticias con distribución realista de estados:
 *   - 12 pendientes  (30%)
 *   - 12 en revisión (30%)
 *   - 10 resueltas   (25%)
 *   -  6 cerradas    (15%)
 *
 * Además inserta 8 casos especiales "fijos" con datos concretos
 * para facilitar las demos y pruebas funcionales.
 */
class IncidenciaSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Asegurarse de que existan residentes
        if (Residente::count() === 0) {
            $this->command->warn('No hay residentes. Ejecutá ResidentesSeeder primero.');
            return;
        }

        $adminId = User::whereHas('roles', fn($q) => $q->where('name', 'Administrador'))
                       ->value('id') ?? 1;

        // ── Casos fijos (datos concretos para demo) ───────────────────────────
        $casosFijos = [
            [
                'titulo'      => 'Fuga de agua en tubería del pasillo — 2do piso',
                'descripcion' => 'El día lunes 5 de mayo noté que había agua escurriendo por la pared del pasillo del segundo piso, frente a la unidad 204. El problema continúa y está mojando el piso. Temo que cause un accidente por resbalón. Solicito reparación urgente.',
                'estado'      => 'en_revision',
                'prioridad'   => 'alta',
                'respuesta_admin' => 'Recibida la denuncia. Se asignó al técnico de mantenimiento para inspección mañana a las 9:00 AM. Le notificaremos el resultado.',
                'atendido_por'   => $adminId,
                'fecha_atencion' => now()->subDays(2),
                'created_at'     => now()->subDays(5),
            ],
            [
                'titulo'      => 'Ruido excesivo en horario nocturno — Unidad 312',
                'descripcion' => 'Desde hace tres semanas los ocupantes de la unidad 312 ponen música a alto volumen después de las 11 PM de manera reiterada. Ya conversé con ellos en dos ocasiones sin resultado. El ruido impide descansar a mi familia, incluyendo a mis dos hijos menores de edad.',
                'estado'      => 'resuelto',
                'prioridad'   => 'alta',
                'respuesta_admin' => 'Se realizó la mediación entre las partes el 12 de mayo. El residente de la unidad 312 firmó el acta de compromiso de respeto al reglamento de convivencia. El caso quedó resuelto satisfactoriamente.',
                'atendido_por'   => $adminId,
                'fecha_atencion' => now()->subDays(10),
                'created_at'     => now()->subDays(20),
            ],
            [
                'titulo'      => 'Luminaria apagada en el estacionamiento — Sector B',
                'descripcion' => 'Hace más de 10 días que la lámpara del sector B del estacionamiento no funciona. Por las noches el área queda completamente a oscuras lo que representa un riesgo de seguridad. Ya lo reporté verbalmente a la administración sin obtener solución.',
                'estado'      => 'resuelto',
                'prioridad'   => 'media',
                'respuesta_admin' => 'El electricista reemplazó el fluorescente y revisó el cableado el día 15 de mayo. El sector B del estacionamiento tiene iluminación completa nuevamente.',
                'atendido_por'   => $adminId,
                'fecha_atencion' => now()->subDays(7),
                'created_at'     => now()->subDays(18),
            ],
            [
                'titulo'      => 'Vehículo desconocido en espacio asignado — Lugar 17',
                'descripcion' => 'Desde el día viernes 9 de mayo hay un automóvil Toyota Corolla de color gris, patente 2541-ABC, estacionado en mi lugar asignado (N° 17). No pertenece a ningún residente que yo conozca. He tenido que dejar mi vehículo en la calle lo cual es un problema de seguridad.',
                'estado'      => 'pendiente',
                'prioridad'   => 'alta',
                'respuesta_admin' => null,
                'atendido_por'   => null,
                'fecha_atencion' => null,
                'created_at'     => now()->subDays(3),
            ],
            [
                'titulo'      => 'Cancha de fútbol con vidrios rotos en el piso',
                'descripcion' => 'El sábado cuando fui a la cancha de fútbol encontré varios pedazos de vidrio rotos distribuidos en el área de juego. Mis hijos estuvieron a punto de cortarse. No sé quién los dejó ahí pero el área es un peligro para los niños que habitualmente la usan.',
                'estado'      => 'en_revision',
                'prioridad'   => 'alta',
                'respuesta_admin' => 'Se constató el problema. El personal de limpieza fue asignado para limpiar el área de inmediato. Se está revisando las cámaras para identificar al responsable.',
                'atendido_por'   => $adminId,
                'fecha_atencion' => now()->subDays(1),
                'created_at'     => now()->subDays(2),
            ],
            [
                'titulo'      => 'Cobro incorrecto en estado de cuenta — Mayo 2025',
                'descripcion' => 'Al revisar mi estado de cuenta del mes de mayo observé un cobro de Bs 150 por concepto de "multa por mora" que no corresponde. Realicé mi pago de expensas el día 3 de mayo antes del vencimiento y tengo el comprobante. Solicito la corrección del cobro.',
                'estado'      => 'cerrado',
                'prioridad'   => 'media',
                'respuesta_admin' => 'Se revisó el historial de pagos y se verificó que el pago fue recibido a tiempo. El cargo por mora fue eliminado del estado de cuenta. Disculpe las molestias ocasionadas.',
                'atendido_por'   => $adminId,
                'fecha_atencion' => now()->subDays(15),
                'created_at'     => now()->subDays(20),
            ],
            [
                'titulo'      => 'Mascota sin correa en área de juegos infantiles',
                'descripcion' => 'En varias ocasiones he observado a un residente que deja suelta a su perro de raza labrador en el área de juegos infantiles sin correa. Los niños pequeños del condominio tienen miedo. Aunque el perro parece tranquilo, la normativa indica que las mascotas deben ir con correa en áreas comunes.',
                'estado'      => 'pendiente',
                'prioridad'   => 'media',
                'respuesta_admin' => null,
                'atendido_por'   => null,
                'fecha_atencion' => null,
                'created_at'     => now()->subDays(4),
            ],
            [
                'titulo'      => 'Goteras en techo del área de lavandería común',
                'descripcion' => 'Con las lluvias de la última semana se detectaron goteras importantes en el techo del área de lavandería común ubicada en la planta baja. Ya se dañó el tomacorriente de una de las lavadoras y hay agua acumulada en el piso. Esto representa un riesgo de electrocución.',
                'estado'      => 'en_revision',
                'prioridad'   => 'alta',
                'respuesta_admin' => 'La situación fue verificada por el administrador. Se desconectaron los tomacorrientes afectados por seguridad y se contactó a la empresa Techex para evaluar la reparación del techo. Fecha estimada de intervención: esta semana.',
                'atendido_por'   => $adminId,
                'fecha_atencion' => now()->subDays(1),
                'created_at'     => now()->subDays(3),
            ],
        ];

        // Insertar casos fijos asignando residente de forma cíclica
        $residentes = Residente::pluck('id')->toArray();
        foreach ($casosFijos as $i => $caso) {
            $caso['residente_id'] = $residentes[$i % count($residentes)];
            Incidencia::create($caso);
        }

        $this->command->info('✔ 8 casos fijos creados.');

        // ── Casos generados con Factory ───────────────────────────────────────
        // 12 pendientes
        Incidencia::factory()->count(12)->pendiente()->create();
        $this->command->info('✔ 12 incidencias pendientes generadas.');

        // 12 en revisión
        Incidencia::factory()->count(12)->enRevision()->create();
        $this->command->info('✔ 12 incidencias en revisión generadas.');

        // 10 resueltas
        Incidencia::factory()->count(10)->resuelto()->create();
        $this->command->info('✔ 10 incidencias resueltas generadas.');

        // 6 cerradas
        Incidencia::factory()->count(6)->cerrado()->create();
        $this->command->info('✔ 6 incidencias cerradas generadas.');

        // 5 adicionales de alta prioridad y pendientes (para que el dashboard las destaque)
        Incidencia::factory()->count(5)->pendiente()->altaPrioridad()->create();
        $this->command->info('✔ 5 incidencias urgentes (alta prioridad) generadas.');

        $total = Incidencia::count();
        $this->command->info("─────────────────────────────────────────");
        $this->command->info("Total de incidencias en BD: {$total}");
        $this->command->info("─────────────────────────────────────────");
    }
}
