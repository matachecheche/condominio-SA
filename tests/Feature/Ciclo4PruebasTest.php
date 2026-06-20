<?php

namespace Tests\Feature;

use App\Models\AreaComun;
use App\Models\Comunicado;
use App\Models\Cuota;
use App\Models\EmpresaExterna;
use App\Models\Mantenimiento;
use App\Models\Residente;
use App\Models\TipoCuota;
use App\Models\Unidad;
use App\Models\User;
use App\Models\Visita;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Casos de Prueba (Caja Negra / Integración) — Documento "Pruebas de
 * Validación" del Condominio San Diego.
 *
 * Esta clase usa SQLite en memoria (ver setUp()) para no afectar en ningún
 * caso la base de datos real configurada en el .env del proyecto.
 *
 * Ejecutar solo estos tests:
 *   php artisan test --filter=Ciclo4PruebasTest
 */
class Ciclo4PruebasTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Fuerza una conexión SQLite en memoria exclusivamente para este test,
     * sin modificar la configuración global del proyecto ni tocar la BD real.
     */
    protected function setUp(): void
    {
        putenv('DB_CONNECTION=sqlite');
        putenv('DB_DATABASE=:memory:');
        $_ENV['DB_CONNECTION'] = 'sqlite';
        $_ENV['DB_DATABASE'] = ':memory:';

        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
        ]);
    }

    /** Crea un usuario Administrador autenticable para las pruebas. */
    protected function crearAdmin(): User
    {
        Role::firstOrCreate(['name' => 'Administrador', 'guard_name' => 'web']);
        $permisos = ['administrar visitas', 'operar porteria'];
        foreach ($permisos as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        $admin = User::factory()->create();
        $admin->assignRole('Administrador');
        $admin->givePermissionTo($permisos);

        return $admin;
    }

    /** Crea un usuario con permisos de Portero (control de acceso). */
    protected function crearPortero(): User
    {
        Role::firstOrCreate(['name' => 'Portero', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'operar porteria', 'guard_name' => 'web']);

        $portero = User::factory()->create();
        $portero->assignRole('Portero');
        $portero->givePermissionTo('operar porteria');

        return $portero;
    }

    /* ═══════════════════════════════════════════════════════════════════
     * 1) Pago-001 — CU7 — Registro de Cuota con Cálculo de Multa Automática
     * ═══════════════════════════════════════════════════════════════════ */
    public function test_pago_001_registro_de_cuota_con_calculo_de_multa_automatica(): void
    {
        $admin = $this->crearAdmin();
        $residente = Residente::factory()->create();

        // Cuota de 500 Bs. vencida hace 12 días (entra en el primer bloque de mora).
        $cuota = Cuota::factory()->create([
            'residente_id' => $residente->id,
            'monto' => 500,
            'estado' => 'pendiente',
            'fecha_emision' => Carbon::now()->subDays(40)->toDateString(),
            'fecha_vencimiento' => Carbon::now()->subDays(12)->toDateString(),
        ]);

        $response = $this->actingAs($admin)->post(route('pagos.store'), [
            'cuota_id' => $cuota->id,
            'monto_pagado' => 500,
            'fecha_pago' => Carbon::now()->toDateString(),
            'metodo' => 'QR',
        ]);

        $response->assertRedirect(route('pagos.index'));

        // El estado de la cuota cambia a "pagado".
        $cuota->refresh();
        $this->assertSame('pagado', $cuota->estado);

        // Se generó automáticamente un comprobante (registro de Pago).
        $this->assertDatabaseHas('pagos', [
            'cuota_id' => $cuota->id,
            'monto_pagado' => 500,
        ]);

        // El sistema aplicó la multa correspondiente por mora (50 Bs. cada
        // bloque de 10 días de atraso → 12 días de atraso = 2 bloques = 100 Bs.).
        $this->assertDatabaseHas('multas', [
            'cuota_id' => $cuota->id,
            'residente_id' => $residente->id,
        ]);

        $multa = $cuota->multas()->first();
        $this->assertNotNull($multa, 'Se esperaba que se generara una multa automática por mora.');
        $this->assertEquals(100.0, (float) $multa->monto);
    }

    /* ═══════════════════════════════════════════════════════════════════
     * 2) Reserva-002 — CU8 — Solicitud de Área Común con Restricción Activa
     * ═══════════════════════════════════════════════════════════════════ */
    public function test_reserva_002_solicitud_de_area_comun_con_restriccion_activa(): void
    {
        $residente = Residente::factory()->create();
        $user = User::factory()->create(['residente_id' => $residente->id]);

        // El residente tiene una deuda vencida (estado: Moroso).
        Cuota::factory()->create([
            'residente_id' => $residente->id,
            'estado' => 'pendiente',
            'fecha_vencimiento' => Carbon::now()->subDays(5)->toDateString(),
        ]);

        $this->assertTrue($residente->tieneMorosidad());

        $areaComun = AreaComun::create([
            'nombre' => 'Club House',
            'estado' => 'activo',
            'monto' => 50,
        ]);

        $fecha = Carbon::now()->addDays(3)->toDateString();

        $response = $this->actingAs($user)->post(route('reservas.store'), [
            'area_comun_id' => $areaComun->id,
            'fecha' => $fecha,
            'hora_inicio' => '10:00',
            'hora_fin' => '12:00',
        ]);

        // El sistema rechaza la solicitud de reserva (vuelve con errores, no redirige al éxito).
        $response->assertSessionHasErrors('morosidad');

        // No se creó ninguna reserva para este residente.
        $this->assertDatabaseMissing('reservas', [
            'residente_id' => $residente->id,
            'area_comun_id' => $areaComun->id,
        ]);

        // El horario permanece disponible: otro residente sin deudas sí puede reservar.
        $otroResidente = Residente::factory()->create();
        $otroUser = User::factory()->create(['residente_id' => $otroResidente->id]);

        $response2 = $this->actingAs($otroUser)->post(route('reservas.store'), [
            'area_comun_id' => $areaComun->id,
            'fecha' => $fecha,
            'hora_inicio' => '13:00',
            'hora_fin' => '15:00',
        ]);

        $response2->assertRedirect(route('reservas.index'));
        $this->assertDatabaseHas('reservas', [
            'residente_id' => $otroResidente->id,
            'area_comun_id' => $areaComun->id,
        ]);
    }

    /* ═══════════════════════════════════════════════════════════════════
     * 3) Visita-003 — CU10 — Registro y Control de Acceso en Portería
     * ═══════════════════════════════════════════════════════════════════ */
    public function test_visita_003_registro_y_control_de_acceso_en_porteria(): void
    {
        $admin = $this->crearAdmin();
        $portero = $this->crearPortero();
        $residente = Residente::factory()->create();

        // El guardia (a través de un usuario con permisos administrativos)
        // registra el ingreso de un visitante para la unidad de destino.
        $fechaInicio = Carbon::now()->addMinutes(1);
        $fechaFin = Carbon::now()->addHours(3);

        $storeResponse = $this->actingAs($admin)->post(route('visitas.store'), [
            'residente_id' => $residente->id,
            'nombre_visitante' => 'Juan Pérez',
            'ci_visitante' => '12345678',
            'motivo' => 'Visita familiar',
            'fecha_inicio' => $fechaInicio->format('Y-m-d\TH:i'),
            'fecha_fin' => $fechaFin->format('Y-m-d\TH:i'),
            'placa_vehiculo' => 'ABC-123',
        ]);

        $visita = Visita::where('ci_visitante', '12345678')->first();
        $this->assertNotNull($visita, 'Se esperaba que la visita quedara registrada.');
        $this->assertSame('pendiente', $visita->estado);

        // El portero registra la hora exacta de entrada.
        $entradaResponse = $this->actingAs($portero)->post(route('visitas.entrada', $visita->id));
        $visita->refresh();

        $this->assertSame('en_curso', $visita->estado);
        $this->assertNotNull($visita->hora_entrada, 'Debe quedar registrada la hora de entrada.');
        $this->assertSame($portero->id, $visita->user_entrada_id);

        // Al retirarse el visitante, el guardia marca la salida.
        $salidaResponse = $this->actingAs($portero)->post(route('visitas.salida', $visita->id));
        $visita->refresh();

        $this->assertSame('finalizada', $visita->estado);
        $this->assertNotNull($visita->hora_salida, 'Debe quedar registrada la hora de salida.');
        $this->assertSame($portero->id, $visita->user_salida_id);

        // El historial de movimientos queda guardado vinculado a la unidad/residente de destino.
        $this->assertDatabaseHas('visitas', [
            'id' => $visita->id,
            'residente_id' => $residente->id,
            'placa_vehiculo' => 'ABC-123',
            'estado' => 'finalizada',
        ]);
    }

    /* ═══════════════════════════════════════════════════════════════════
     * 4) Ocupacion-004 — CU13 — Asignación de Inquilino a Vivienda
     * ═══════════════════════════════════════════════════════════════════ */
    public function test_ocupacion_004_asignacion_de_inquilino_a_vivienda(): void
    {
        $admin = $this->crearAdmin();
        $residente = Residente::factory()->create();
        $unidad = Unidad::factory()->create([
            'codigo' => 'U-900',
            'estado' => 'activa',
            'residente_id' => null,
        ]);

        $response = $this->actingAs($admin)->post(route('unidades.store'), [
            'codigo' => 'U-901',
            'capacidad' => 4,
            'personas_por_unidad' => 2,
            'vehiculos' => 1,
            'tiene_mascotas' => 0,
            'estado' => 'activa',
            'tipo_ocupacion' => 'Inquilino',
            'residente_id' => $residente->id,
        ]);

        $response->assertRedirect(route('unidades.index'));

        // El sistema vincula al residente a la unidad y la deja como ocupada.
        $this->assertDatabaseHas('unidades', [
            'codigo' => 'U-901',
            'residente_id' => $residente->id,
            'tipo_ocupacion' => 'Inquilino',
            'estado' => 'activa',
        ]);

        // El sistema impide asignar al mismo residente a una segunda unidad activa.
        $response2 = $this->actingAs($admin)->post(route('unidades.store'), [
            'codigo' => 'U-902',
            'capacidad' => 3,
            'personas_por_unidad' => 1,
            'vehiculos' => 0,
            'tiene_mascotas' => 0,
            'estado' => 'activa',
            'tipo_ocupacion' => 'Inquilino',
            'residente_id' => $residente->id,
        ]);

        $response2->assertSessionHasErrors('residente_id');
        $this->assertDatabaseMissing('unidades', ['codigo' => 'U-902']);
    }

    /* ═══════════════════════════════════════════════════════════════════
     * 5) Mant-005 — CU9 — Registro de Orden y Finalización de Servicio
     * ═══════════════════════════════════════════════════════════════════ */
    public function test_mant_005_registro_de_orden_y_finalizacion_de_servicio(): void
    {
        $admin = $this->crearAdmin();
        $empresa = EmpresaExterna::factory()->create(['servicio' => 'Limpieza']);

        // El administrador registra la orden indicando el área afectada
        // (estado=1 representa "Programado" en este sistema).
        $response = $this->actingAs($admin)->post(route('mantenimientos.store'), [
            'descripcion' => 'Limpieza y mantenimiento de la piscina',
            'estado' => 1,
            'fecha_hora' => Carbon::now()->addDays(2)->format('Y-m-d H:i:s'),
            'monto' => 350,
            'usuario_id' => $admin->id,
            'empresaExterna_id' => $empresa->id,
        ]);

        $response->assertRedirect(route('mantenimientos.index'));

        $mantenimiento = Mantenimiento::where('descripcion', 'Limpieza y mantenimiento de la piscina')->first();
        $this->assertNotNull($mantenimiento);
        $this->assertEquals(1, $mantenimiento->estado);
        $this->assertEquals($empresa->id, $mantenimiento->empresaExterna_id);

        // Al concluir la tarea, el administrador cambia el estado a "Finalizado" (0).
        $updateResponse = $this->actingAs($admin)->put(route('mantenimientos.update', $mantenimiento->id), [
            'descripcion' => $mantenimiento->descripcion,
            'estado' => 0,
            'fecha_hora' => $mantenimiento->fecha_hora,
            'monto' => $mantenimiento->monto,
            'usuario_id' => $admin->id,
        ]);

        $updateResponse->assertRedirect(route('mantenimientos.index'));

        $mantenimiento->refresh();
        $this->assertEquals(0, $mantenimiento->estado);

        // El costo queda asociado al historial (registro persistido con su monto).
        $this->assertDatabaseHas('mantenimientos', [
            'id' => $mantenimiento->id,
            'monto' => 350,
            'estado' => 0,
        ]);
    }

    /* ═══════════════════════════════════════════════════════════════════
     * 6) Com-006 — CU11 — Redacción y Difusión de Aviso Oficial
     * ═══════════════════════════════════════════════════════════════════ */
    public function test_com_006_redaccion_y_difusion_de_aviso_oficial(): void
    {
        $admin = $this->crearAdmin();

        $response = $this->actingAs($admin)->post(route('comunicados.store'), [
            'titulo' => 'Mantenimiento de bomba de agua',
            'contenido' => 'Se realizará mantenimiento preventivo de la bomba de agua el día sábado.',
            'tipo' => 'Informativo',
        ]);

        $response->assertRedirect(route('comunicados.index'));

        // El anuncio queda archivado (persistido) para consultas futuras.
        $this->assertDatabaseHas('comunicados', [
            'titulo' => 'Mantenimiento de bomba de agua',
            'tipo' => 'Informativo',
        ]);

        // El anuncio se publica inmediatamente: aparece en el listado público.
        $indexResponse = $this->actingAs($admin)->get(route('comunicados.index'));
        $indexResponse->assertOk();
        $indexResponse->assertSee('Mantenimiento de bomba de agua');

        // Se generó una notificación asociada a la difusión del comunicado.
        $this->assertDatabaseHas('notificaciones', [
            'titulo' => 'Nuevo Comunicado',
        ]);
    }
}
