#!/usr/bin/env bash
###############################################################################
# ciclo4_pruebas.sh — Condominio SA
#
# Implementa la lógica de negocio que faltaba para que el sistema cumpla
# con los Casos de Prueba (Caja Negra / Integración) documentados, y agrega
# tests automatizados (PHPUnit) que replican esos 6 casos:
#
#   Pago-001     (CU7)  Multa automática por mora al registrar un pago tardío
#   Reserva-002  (CU8)  Bloqueo de reserva de área común si el residente
#                       tiene cuotas vencidas (moroso)
#   Visita-003   (CU10) Registro y control de acceso en portería
#   Ocupacion-004(CU13) Asignación de residente a unidad habitacional
#   Mant-005     (CU9)  Registro y finalización de orden de mantenimiento
#   Com-006      (CU11) Redacción y difusión de comunicado interno
#
# Lo que este script AGREGA (lógica de negocio nueva, no existía):
#   - App\Models\Cuota::calcularMultaPorMora()
#   - App\Models\Cuota::aplicarMultaSiCorresponde()
#   - App\Models\Residente::tieneMorosidad()
#   - Parche puntual en PagoController::store() (aplica la multa automática)
#   - Parche puntual en ReservaController::store() (bloquea por morosidad)
#
# Lo que este script AGREGA (tests, no existían):
#   - tests/Feature/Ciclo4PruebasTest.php (los 6 casos de prueba)
#
# Este script usa SQLite en memoria exclusivamente para los tests, por lo
# que NO toca ni modifica la base de datos real configurada en tu .env.
#
# Uso:
#   1) Copiar este archivo a la raíz del proyecto Laravel (condominio-SA)
#   2) chmod +x ciclo4_pruebas.sh
#   3) ./ciclo4_pruebas.sh
#   4) php artisan test --filter=Ciclo4PruebasTest   (o: vendor/bin/phpunit)
###############################################################################
set -e

if [ ! -f "artisan" ]; then
    echo "❌ Error: no se encontró 'artisan'. Ejecuta este script desde la raíz del proyecto Laravel."
    exit 1
fi

echo "🚀 Implementando lógica de negocio y tests de los Casos de Prueba..."

###############################################################################
# 1) MODELO Cuota — multa automática por mora (CU7)
###############################################################################
echo "🧩 Agregando lógica de multa automática a app/Models/Cuota.php (CU7)"

CUOTA_FILE="app/Models/Cuota.php"
if [ -f "$CUOTA_FILE" ]; then
    if ! grep -q "function calcularMultaPorMora" "$CUOTA_FILE"; then
        python3 - "$CUOTA_FILE" << 'PYEOF'
import sys

path = sys.argv[1]
with open(path, "r", encoding="utf-8") as f:
    content = f.read()

old = """    public function multas()
    {
        return $this->hasMany(Multa::class);
    }
}"""

new = """    public function multas()
    {
        return $this->hasMany(Multa::class);
    }

    /**
     * Calcula el monto de la multa por mora según la fecha en que se realiza
     * el pago, comparada con la fecha de vencimiento de la cuota.
     *
     * Regla de negocio (según documento de pruebas Pago-001):
     *   50 Bs. de multa por cada bloque de 10 días de atraso (o fracción).
     *
     * @param  \\DateTimeInterface|string  $fechaPago
     * @return float
     */
    public function calcularMultaPorMora($fechaPago): float
    {
        $fechaPago = $fechaPago instanceof \\Carbon\\Carbon
            ? $fechaPago
            : \\Carbon\\Carbon::parse($fechaPago);

        $vencimiento = \\Carbon\\Carbon::parse($this->fecha_vencimiento);

        if ($fechaPago->lessThanOrEqualTo($vencimiento)) {
            return 0.0;
        }

        $diasAtraso = $vencimiento->diffInDays($fechaPago);
        $bloquesDeMora = (int) ceil($diasAtraso / 10);

        return $bloquesDeMora * 50.0;
    }

    /**
     * Aplica (crea) la multa por mora correspondiente a esta cuota, si el
     * pago se realizó después de la fecha de vencimiento. No duplica la
     * multa si ya existe una multa asociada a esta cuota.
     *
     * @param  \\DateTimeInterface|string  $fechaPago
     * @return \\App\\Models\\Multa|null
     */
    public function aplicarMultaSiCorresponde($fechaPago): ?Multa
    {
        $montoMulta = $this->calcularMultaPorMora($fechaPago);

        if ($montoMulta <= 0) {
            return null;
        }

        if ($this->multas()->exists()) {
            return $this->multas()->latest('id')->first();
        }

        return $this->multas()->create([
            'motivo'       => 'Mora en el pago de la cuota: ' . $this->titulo,
            'monto'        => $montoMulta,
            'fechaEmision' => now()->toDateString(),
            'fechaLimite'  => now()->addDays(10)->toDateString(),
            'estado'       => 'pendiente',
            'residente_id' => $this->residente_id,
        ]);
    }
}"""

assert old in content, "No se encontró el bloque esperado al final de Cuota.php"
content = content.replace(old, new)

with open(path, "w", encoding="utf-8") as f:
    f.write(content)

print("   ✔ app/Models/Cuota.php actualizado (calcularMultaPorMora, aplicarMultaSiCorresponde)")
PYEOF
    else
        echo "   ⏭️  app/Models/Cuota.php ya tiene calcularMultaPorMora(), no se modifica"
    fi
else
    echo "   ❌ No se encontró $CUOTA_FILE"
    exit 1
fi

###############################################################################
# 2) MODELO Residente — detección de morosidad (CU7 / CU8)
###############################################################################
echo "🧩 Agregando tieneMorosidad() a app/Models/Residente.php (CU8)"

RESIDENTE_FILE="app/Models/Residente.php"
if [ -f "$RESIDENTE_FILE" ]; then
    if ! grep -q "function tieneMorosidad" "$RESIDENTE_FILE"; then
        awk '
            BEGIN { last=0 }
            { lines[NR]=$0; last=NR }
            END {
                for (i=1; i<=last; i++) {
                    if (i == last) {
                        print "    /**"
                        print "     * Indica si el residente tiene al menos una cuota vencida y no pagada."
                        print "     * Se utiliza para restringir el acceso a servicios como reservas de"
                        print "     * áreas comunes mientras exista morosidad (ver caso Reserva-002)."
                        print "     *"
                        print "     * @return bool"
                        print "     */"
                        print "    public function tieneMorosidad(): bool"
                        print "    {"
                        print "        return $this->cuotas()"
                        print "            ->where(\x27estado\x27, \x27!=\x27, \x27pagado\x27)"
                        print "            ->whereDate(\x27fecha_vencimiento\x27, \x27<\x27, now()->toDateString())"
                        print "            ->exists();"
                        print "    }"
                        print lines[i]
                    } else {
                        print lines[i]
                    }
                }
            }
        ' "$RESIDENTE_FILE" > "$RESIDENTE_FILE.tmp"
        mv "$RESIDENTE_FILE.tmp" "$RESIDENTE_FILE"
        echo "   ✔ app/Models/Residente.php actualizado (tieneMorosidad)"
    else
        echo "   ⏭️  app/Models/Residente.php ya tiene tieneMorosidad(), no se modifica"
    fi
else
    echo "   ❌ No se encontró $RESIDENTE_FILE"
    exit 1
fi

###############################################################################
# 3) PagoController::store() — aplicar multa automática al registrar el pago (CU7)
###############################################################################
echo "🧩 Conectando el cálculo de multa automática en PagoController::store() (CU7)"

PAGO_CTRL="app/Http/Controllers/PagoController.php"
if [ -f "$PAGO_CTRL" ]; then
    if ! grep -q "aplicarMultaSiCorresponde" "$PAGO_CTRL"; then
        python3 - "$PAGO_CTRL" << 'PYEOF'
import sys

path = sys.argv[1]
with open(path, "r", encoding="utf-8") as f:
    content = f.read()

old = """        // Si el pago cubre el monto total de la cuota, actualizar el estado
        if ($request->monto_pagado >= $cuota->monto) {
            $cuota->estado = 'pagado';
            $cuota->save();
        }
        $this->registrarEnBitacora('Pago registrado', $pago->id);

        return redirect()->route('pagos.index')->with('success', 'Pago registrado exitosamente.');
    }
}"""

new = """        // Si el pago se realizó después del vencimiento, se aplica la multa
        // automática por mora (CU7 - caso de prueba Pago-001).
        $multaGenerada = $cuota->aplicarMultaSiCorresponde($request->fecha_pago);

        // Si el pago cubre el monto total de la cuota, actualizar el estado
        if ($request->monto_pagado >= $cuota->monto) {
            $cuota->estado = 'pagado';
            $cuota->save();
        }
        $this->registrarEnBitacora('Pago registrado', $pago->id);

        $mensaje = 'Pago registrado exitosamente.';
        if ($multaGenerada) {
            $mensaje .= ' Se aplicó una multa por mora de Bs. ' . number_format($multaGenerada->monto, 2) . '.';
        }

        return redirect()->route('pagos.index')->with('success', $mensaje);
    }
}"""

assert old in content, "No se encontró el bloque final esperado de PagoController::store()"
content = content.replace(old, new)

with open(path, "w", encoding="utf-8") as f:
    f.write(content)

print("   ✔ app/Http/Controllers/PagoController.php actualizado (multa automática conectada)")
PYEOF
    else
        echo "   ⏭️  PagoController.php ya invoca aplicarMultaSiCorresponde(), no se modifica"
    fi
else
    echo "   ❌ No se encontró $PAGO_CTRL"
    exit 1
fi

###############################################################################
# 4) ReservaController::store() — bloqueo por morosidad (CU8)
###############################################################################
echo "🧩 Conectando el bloqueo de reservas por morosidad en ReservaController::store() (CU8)"

RESERVA_CTRL="app/Http/Controllers/ReservaController.php"
if [ -f "$RESERVA_CTRL" ]; then
    if ! grep -q "tieneMorosidad" "$RESERVA_CTRL"; then
        python3 - "$RESERVA_CTRL" << 'PYEOF'
import sys

path = sys.argv[1]
with open(path, "r", encoding="utf-8") as f:
    content = f.read()

old = """        if (!$user->residente_id) {
            return back()->withErrors([
                'residente_id' => 'El usuario no tiene residente asignado. Usuario ID: ' . $user->id
            ])->withInput();
        }

        // Crear reserva"""

new = """        if (!$user->residente_id) {
            return back()->withErrors([
                'residente_id' => 'El usuario no tiene residente asignado. Usuario ID: ' . $user->id
            ])->withInput();
        }

        // Bloquear la reserva si el residente tiene cuotas vencidas sin pagar
        // (CU8 - caso de prueba Reserva-002: Validación de Morosidad).
        $residenteSolicitante = \\App\\Models\\Residente::find($user->residente_id);
        if ($residenteSolicitante && $residenteSolicitante->tieneMorosidad()) {
            return back()->withErrors([
                'morosidad' => 'No es posible registrar la reserva: el residente tiene cuotas vencidas pendientes de pago.',
            ])->withInput();
        }

        // Crear reserva"""

count = content.count(old)
assert count == 1, f"El ancla debe aparecer exactamente una vez (encontrado: {count})"
content = content.replace(old, new)

with open(path, "w", encoding="utf-8") as f:
    f.write(content)

print("   ✔ app/Http/Controllers/ReservaController.php actualizado (bloqueo por morosidad conectado)")
PYEOF
    else
        echo "   ⏭️  ReservaController.php ya valida tieneMorosidad(), no se modifica"
    fi
else
    echo "   ❌ No se encontró $RESERVA_CTRL"
    exit 1
fi

###############################################################################
# 5) TESTS — tests/Feature/Ciclo4PruebasTest.php
#    Replica los 6 Casos de Prueba documentados, usando SQLite en memoria
#    para no afectar la base de datos real del proyecto.
###############################################################################
mkdir -p tests/Feature

echo "🧪 Creando tests/Feature/Ciclo4PruebasTest.php"
cat > tests/Feature/Ciclo4PruebasTest.php << 'EOF'
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
EOF

###############################################################################
# 6) Verificación rápida del driver SQLite (no destructivo)
###############################################################################
if command -v php > /dev/null 2>&1; then
    if php -m | grep -qi "pdo_sqlite"; then
        echo "✔ Extensión pdo_sqlite detectada: los tests podrán correr en memoria sin tocar tu base de datos real."
    else
        echo "⚠️  No se detectó la extensión pdo_sqlite en PHP. Los tests de Ciclo4PruebasTest la necesitan para"
        echo "   correr en memoria sin afectar tu base de datos real. Instálala con, por ejemplo:"
        echo "     sudo apt-get install -y php-sqlite3"
    fi
else
    echo "⚠️  No se encontró el binario 'php' en el PATH; no se pudo verificar la extensión pdo_sqlite."
fi

###############################################################################
# FIN
###############################################################################
echo ""
echo "✅ Lógica de negocio y tests del documento de pruebas aplicados correctamente."
echo ""
echo "Resumen — Lógica de negocio agregada:"
echo "  CU7  · Multa automática por mora     -> Cuota::calcularMultaPorMora() / aplicarMultaSiCorresponde()"
echo "                                          conectado en PagoController::store()"
echo "  CU8  · Bloqueo de reserva por mora    -> Residente::tieneMorosidad()"
echo "                                          conectado en ReservaController::store()"
echo "  CU9, CU10, CU11, CU13                 -> ya estaban implementados; no se modificó su lógica,"
echo "                                          solo se agregaron tests que los validan."
echo ""
echo "Resumen — Tests agregados (tests/Feature/Ciclo4PruebasTest.php):"
echo "  Pago-001      CU7  Multa automática por mora"
echo "  Reserva-002   CU8  Bloqueo de reserva por morosidad"
echo "  Visita-003    CU10 Registro y control de acceso en portería"
echo "  Ocupacion-004 CU13 Asignación de residente a unidad habitacional"
echo "  Mant-005      CU9  Registro y finalización de orden de mantenimiento"
echo "  Com-006       CU11 Redacción y difusión de comunicado interno"
echo ""
echo "IMPORTANTE — Seguridad de los tests:"
echo "  Los tests usan SQLite EN MEMORIA exclusivamente (forzado dentro del propio archivo de"
echo "  test). NO modifican phpunit.xml ni tu .env, por lo que tu base de datos real (la que"
echo "  usas para desarrollo) no se ve afectada al ejecutar 'php artisan test'."
echo ""
echo "Próximos pasos:"
echo "  1) Revisa los cambios con: git status / git diff"
echo "  2) Corre SOLO los tests de este ciclo:"
echo "       php artisan test --filter=Ciclo4PruebasTest"
echo "     o bien:"
echo "       vendor/bin/phpunit --filter=Ciclo4PruebasTest"
echo "  3) Si algún test falla por datos de tu entorno (roles/permisos ya existentes con otro"
echo "     guard, etc.), revisa tests/Feature/Ciclo4PruebasTest.php; está pensado para crear"
echo "     todo lo que necesita desde cero en una base de datos en memoria."
echo ""
