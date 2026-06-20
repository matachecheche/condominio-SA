<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Reclamo;
use App\Models\Residente;
use App\Models\User;

/**
 * Seeder de Reclamos Administrativos — CU18 — Condominio San Diego
 */
class ReclamoSeeder extends Seeder
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

        $reclamos = [
            [
                'titulo' => 'Cobro duplicado de cuota de mantenimiento — Mayo 2026',
                'contenido' => 'En mi estado de cuenta figura el cobro de la cuota de mantenimiento de mayo dos veces. Solicito la revisión y corrección del monto adeudado.',
                'estado' => 'resuelto',
                'respuesta_admin' => 'Se verificó el error en el sistema de facturación y se anuló el cobro duplicado. El estado de cuenta ya fue corregido.',
                'atendido_por' => $adminId,
                'fecha_atencion' => now()->subDays(4),
                'created_at' => now()->subDays(9),
            ],
            [
                'titulo' => 'Demora en la entrega de comprobante de pago',
                'contenido' => 'Realicé el pago de mi cuota hace una semana y aún no recibo el comprobante correspondiente por parte de administración.',
                'estado' => 'en_revision',
                'respuesta_admin' => 'Estamos verificando con contabilidad la generación del comprobante. Le contactaremos a la brevedad.',
                'atendido_por' => $adminId,
                'fecha_atencion' => now()->subDay(),
                'created_at' => now()->subDays(3),
            ],
            [
                'titulo' => 'Disconformidad con el monto de la multa aplicada',
                'contenido' => 'Considero que la multa aplicada por uso indebido del área social no corresponde a la situación real ocurrida. Solicito revisión del caso.',
                'estado' => 'pendiente',
                'respuesta_admin' => null,
                'atendido_por' => null,
                'fecha_atencion' => null,
                'created_at' => now()->subDays(2),
            ],
            [
                'titulo' => 'Solicitud de aclaración sobre gasto extraordinario',
                'contenido' => 'En el informe administrativo del trimestre aparece un gasto extraordinario sin detalle. Solicito se aclare el concepto de dicho gasto.',
                'estado' => 'rechazado',
                'respuesta_admin' => 'El gasto corresponde a la reparación de la bomba de agua, documentado en acta de asamblea del 10 de abril. No procede observación adicional.',
                'atendido_por' => $adminId,
                'fecha_atencion' => now()->subDays(6),
                'created_at' => now()->subDays(12),
            ],
            [
                'titulo' => 'Reclamo por descuento no aplicado por pronto pago',
                'contenido' => 'Pagué mi cuota dentro de la fecha límite para el descuento por pronto pago, pero el sistema no me aplicó el beneficio correspondiente.',
                'estado' => 'pendiente',
                'respuesta_admin' => null,
                'atendido_por' => null,
                'fecha_atencion' => null,
                'created_at' => now()->subDay(),
            ],
        ];

        foreach ($reclamos as $i => $data) {
            $data['residente_id'] = $residentes[$i % count($residentes)];
            Reclamo::create($data);
        }

        $this->command->info('✔ ' . count($reclamos) . ' reclamos administrativos generados.');
    }
}
