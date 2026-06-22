#!/usr/bin/env bash
# ============================================================================
# solucionar_pagos_y_voz.sh
#
# Soluciona en condominio-SA:
#
#   PROBLEMA 1 — "Pagar con Stripe" fallaba / createMulta daba error:
#     config/services.php NO tenía el bloque 'stripe', así que
#     config('services.stripe.secret') era null y Stripe rechazaba
#     cualquier intento de pago. Además stripeSuccess()/stripeSuccessMulta()
#     marcaban el pago como "aprobado" solo por visitar la URL de retorno
#     (GET), sin verificar contra la API de Stripe que el pago se completó
#     de verdad (cualquiera podía "pagar gratis" visitando la URL a mano).
#
#   PROBLEMA 2 — "Registrar Pago" (formulario manual de admin) no tiene
#     opción de pagar multas, solo cuotas, y su <select> de método no
#     incluye Stripe como referencia informativa.
#
#   PROBLEMA 3 — Falta el asistente de voz (botón flotante + reconocimiento
#     de voz del navegador) para navegar el sistema con comandos hablados.
#
# Todo idempotente: puedes correr este script varias veces sin duplicar
# nada. Ejecutar desde la raíz del proyecto (donde está 'artisan').
#
# Después de correrlo en Codespaces necesitas:
#   composer install
#   php artisan migrate
#   Editar .env con tus claves de prueba de Stripe
#   php artisan config:clear
# ============================================================================
set -e

if [ ! -f "artisan" ]; then
    echo "ERROR: no se encontró 'artisan' en el directorio actual."
    echo "Ejecuta este script desde la raíz del proyecto Laravel."
    exit 1
fi

echo "== Arreglando Stripe (CU7), agregando Registrar Pago de multas y asistente de voz =="
echo ""

# ----------------------------------------------------------------------------
# [1/9] config/services.php: agregar bloque 'stripe' (la causa raíz del bug)
# ----------------------------------------------------------------------------
SERVICES_FILE="config/services.php"

if grep -q "'stripe' =>" "$SERVICES_FILE"; then
    echo "[1/9] config/services.php ya tiene el bloque 'stripe'. Sin cambios."
else
    echo "[1/9] Agregando bloque 'stripe' a config/services.php..."
    python3 - "$SERVICES_FILE" <<'PYEOF'
import sys
path = sys.argv[1]
with open(path, "r", encoding="utf-8") as f:
    content = f.read()

bloque_stripe = """
    /*
    |--------------------------------------------------------------------------
    | Stripe (CU7 — Pago de cuotas y multas con tarjeta)
    |--------------------------------------------------------------------------
    | Claves de prueba: https://dashboard.stripe.com/test/apikeys
    */
    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'currency' => env('STRIPE_CURRENCY', 'bob'),
    ],

"""

if content.rstrip().endswith("];"):
    idx = content.rstrip().rfind("];")
    content = content[:idx] + bloque_stripe.strip("\n") + "\n\n" + content[idx:]
else:
    content = content + bloque_stripe

with open(path, "w", encoding="utf-8") as f:
    f.write(content)

print("Bloque 'stripe' agregado a config/services.php.")
PYEOF
fi

# ----------------------------------------------------------------------------
# [2/9] Variables de entorno: .env y .env.example (si existen)
# ----------------------------------------------------------------------------
echo ""
STRIPE_ENV_BLOCK="
# CU7 — Pasarela de pagos (Stripe Checkout)
# Claves de PRUEBA desde https://dashboard.stripe.com/test/apikeys
STRIPE_KEY=pk_test_REEMPLAZAR
STRIPE_SECRET=sk_test_REEMPLAZAR
STRIPE_CURRENCY=bob
"

for ENV_FILE in ".env" ".env.example"; do
    if [ -f "$ENV_FILE" ]; then
        if grep -q "^STRIPE_SECRET" "$ENV_FILE"; then
            echo "[2/9] $ENV_FILE ya tiene variables STRIPE_*. Sin cambios."
        else
            echo "[2/9] Agregando variables STRIPE_* a $ENV_FILE..."
            printf '%s' "$STRIPE_ENV_BLOCK" >> "$ENV_FILE"
        fi
    else
        echo "[2/9] $ENV_FILE no existe, se omite."
    fi
done

# ----------------------------------------------------------------------------
# [3/9] Migración nueva: columnas de Stripe en `pagos` (para verificar el
#       pago contra la API real en vez de confiar en el GET de retorno)
# ----------------------------------------------------------------------------
echo ""
MIGRATION_GLOB="database/migrations/*_add_stripe_columns_to_pagos_table.php"
# shellcheck disable=SC2086
EXISTING_MIGRATION=$(ls $MIGRATION_GLOB 2>/dev/null | head -n1 || true)

if [ -n "$EXISTING_MIGRATION" ]; then
    echo "[3/9] Migración de columnas Stripe ya existe. Sin cambios."
else
    TIMESTAMP=$(date +%Y_%m_%d_%H%M%S)
    NEW_MIGRATION="database/migrations/${TIMESTAMP}_add_stripe_columns_to_pagos_table.php"
    echo "[3/9] Creando migración $NEW_MIGRATION..."
    cat > "$NEW_MIGRATION" <<'PHPEOF'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * CU7 — agrega la columna necesaria para verificar pagos de Stripe contra
 * la API real (en vez de confiar ciegamente en la URL de retorno). No
 * modifica ni borra columnas existentes de `pagos`.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            if (!Schema::hasColumn('pagos', 'stripe_session_id')) {
                $table->string('stripe_session_id')->nullable()->unique()->after('comprobante');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            if (Schema::hasColumn('pagos', 'stripe_session_id')) {
                $table->dropColumn('stripe_session_id');
            }
        });
    }
};
PHPEOF
    echo "Migración creada."
fi

# ----------------------------------------------------------------------------
# [4/9] Modelo Pago: agregar stripe_session_id a $fillable
# ----------------------------------------------------------------------------
echo ""
PAGO_MODEL="app/Models/Pago.php"

if grep -q "stripe_session_id" "$PAGO_MODEL"; then
    echo "[4/9] app/Models/Pago.php ya tiene el campo Stripe en \$fillable. Sin cambios."
else
    echo "[4/9] Agregando stripe_session_id a \$fillable..."
    python3 - "$PAGO_MODEL" <<'PYEOF'
import sys
path = sys.argv[1]
with open(path, "r", encoding="utf-8") as f:
    content = f.read()

marker = "        'multa_id',\n    ];"
reemplazo = "        'multa_id',\n        'stripe_session_id',\n    ];"

if marker not in content:
    print("ADVERTENCIA: no se encontró el cierre de \\$fillable esperado en Pago.php. Revísalo manualmente.")
    sys.exit(0)

content = content.replace(marker, reemplazo, 1)
with open(path, "w", encoding="utf-8") as f:
    f.write(content)
print("Campo agregado a \\$fillable.")
PYEOF
fi

# ----------------------------------------------------------------------------
# [5/9] PagoController: reescribir métodos de Stripe para que SÍ verifiquen
#       el pago contra la API (no solo el GET de retorno) y manejen errores
# ----------------------------------------------------------------------------
echo ""
PAGO_CONTROLLER="app/Http/Controllers/PagoController.php"

if grep -q "checkout->sessions->retrieve" "$PAGO_CONTROLLER"; then
    echo "[5/9] PagoController ya tiene la verificación segura de Stripe. Sin cambios."
else
    echo "[5/9] Reescribiendo métodos de Stripe en PagoController (verificación real + manejo de errores)..."

    python3 - "$PAGO_CONTROLLER" <<'PYEOF'
import sys
path = sys.argv[1]
with open(path, "r", encoding="utf-8") as f:
    content = f.read()

old_imports = "use Stripe\\Stripe;\nuse Stripe\\Checkout\\Session;\n"
new_imports = (
    "use Stripe\\StripeClient;\n"
    "use Illuminate\\Support\\Facades\\Log;\n"
)

if old_imports in content:
    content = content.replace(old_imports, new_imports, 1)
elif "use Stripe\\StripeClient;" not in content:
    content = content.replace(
        "use App\\Models\\Pago;",
        "use App\\Models\\Pago;\n" + new_imports,
        1,
    )

with open(path, "w", encoding="utf-8") as f:
    f.write(content)
print("Imports de Stripe actualizados.")
PYEOF

    python3 - "$PAGO_CONTROLLER" <<'PYEOF'
import sys
path = sys.argv[1]
with open(path, "r", encoding="utf-8") as f:
    content = f.read()

bloque_viejo_inicio = "    public function pagoStripe(Request $request)\n    {"
bloque_viejo_fin_marker = "        return redirect()\n            ->route('pagos.mis_multa')  // o donde muestres “mis multas”\n            ->with('success', 'Pago de multa exitoso con Stripe.');\n    }\n"

idx_inicio = content.find(bloque_viejo_inicio)
idx_fin = content.find(bloque_viejo_fin_marker)

if idx_inicio == -1 or idx_fin == -1:
    print("ADVERTENCIA: no se encontraron los métodos de Stripe esperados. Revisa PagoController.php manualmente.")
    sys.exit(0)

idx_fin_real = idx_fin + len(bloque_viejo_fin_marker)

bloque_nuevo = '''    /** Cliente de Stripe usando la clave secreta configurada en services.php. */
    private function stripe(): StripeClient
    {
        return new StripeClient(config('services.stripe.secret'));
    }

    /** CU7 — Pago de cuota con tarjeta vía Stripe Checkout. */
    public function pagoStripe(Request $request)
    {
        $request->validate(['cuota_id' => 'required|exists:cuotas,id']);

        $cuota = Cuota::findOrFail($request->cuota_id);

        if (auth()->user()->residente_id !== $cuota->residente_id) {
            abort(403);
        }

        $moneda = config('services.stripe.currency', 'bob');

        try {
            $session = $this->stripe()->checkout->sessions->create([
                'mode' => 'payment',
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'quantity' => 1,
                    'price_data' => [
                        'currency' => $moneda,
                        'unit_amount' => (int) round($cuota->monto * 100),
                        'product_data' => [
                            'name' => 'Pago de cuota: ' . $cuota->titulo,
                        ],
                    ],
                ]],
                'success_url' => route('pagos.stripe.success', ['cuota' => $cuota->id]) . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('pagos.stripe.cancel'),
            ]);
        } catch (\\Throwable $e) {
            Log::warning('Error creando sesión de Stripe (cuota): ' . $e->getMessage());

            return back()->with('error', 'No se pudo iniciar el pago con Stripe. Verifica que las claves STRIPE_KEY/STRIPE_SECRET estén configuradas en .env e inténtalo de nuevo.');
        }

        return redirect()->away($session->url);
    }

    /**
     * Retorno desde Stripe tras el pago de una cuota. Verifica DIRECTO
     * contra la API de Stripe antes de marcar el pago como aprobado — no
     * confía solo en que el usuario haya llegado a esta URL (eso sería
     * inseguro: cualquiera podría visitarla manualmente y "pagar gratis").
     */
    public function stripeSuccess(Request $request, $cuotaId)
    {
        $sessionId = $request->query('session_id');
        $cuota = Cuota::findOrFail($cuotaId);

        if (!$sessionId) {
            return redirect()->route('pagos.mis_cuotas')->with('error', 'No se recibió la sesión de pago de Stripe.');
        }

        try {
            $session = $this->stripe()->checkout->sessions->retrieve($sessionId);
        } catch (\\Throwable $e) {
            Log::warning('No se pudo verificar la sesión de Stripe (cuota): ' . $e->getMessage());

            return redirect()->route('pagos.mis_cuotas')->with('error', 'No se pudo verificar el pago con Stripe. Si ya pagaste, contacta a administración.');
        }

        if ($session->payment_status !== 'paid') {
            return redirect()->route('pagos.mis_cuotas')->with('error', 'El pago no se completó. Si el cobro aparece en tu tarjeta, contacta a administración.');
        }

        // Idempotente: si ya existe un pago aprobado con esta sesión, no se duplica.
        $pago = Pago::firstOrCreate(
            ['stripe_session_id' => $sessionId],
            [
                'cuota_id' => $cuota->id,
                'monto_pagado' => $cuota->monto,
                'fecha_pago' => now(),
                'metodo' => 'Stripe',
                'estado' => 'aprobado',
                'user_id' => auth()->id(),
            ]
        );

        if ($pago->estado !== 'aprobado') {
            $pago->update(['estado' => 'aprobado']);
        }

        $multaGenerada = $cuota->aplicarMultaSiCorresponde($pago->fecha_pago);
        if ($pago->monto_pagado >= $cuota->monto) {
            $cuota->estado = 'pagado';
            $cuota->save();
        }
        $this->registrarEnBitacora(
            'Pago de cuota confirmado vía Stripe' . ($multaGenerada ? ' (con multa por mora aplicada)' : ''),
            $pago->id
        );

        return redirect()->route('pagos.mis_cuotas')->with('success', 'Pago realizado exitosamente con Stripe.');
    }

    /** CU7 — Pago de multa con tarjeta vía Stripe Checkout. */
    public function pagoStripeMulta(Request $request)
    {
        $request->validate(['multa_id' => 'required|exists:multas,id']);

        $multa = Multa::findOrFail($request->multa_id);
        $user  = auth()->user();

        $esResidente = $user->residente_id && $user->residente_id === $multa->residente_id;
        $esEmpleado  = $user->empleado_id  && $user->empleado_id  === $multa->empleado_id;
        if (! $user->hasRole('ADMINISTRADOR') && ! ($esResidente || $esEmpleado)) {
            abort(403);
        }

        $moneda = config('services.stripe.currency', 'bob');

        try {
            $session = $this->stripe()->checkout->sessions->create([
                'mode' => 'payment',
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'quantity' => 1,
                    'price_data' => [
                        'currency' => $moneda,
                        'unit_amount' => (int) round($multa->monto * 100),
                        'product_data' => [
                            'name' => 'Pago de multa: ' . $multa->motivo,
                        ],
                    ],
                ]],
                'success_url' => route('pagos.stripe.success.multa', ['multa' => $multa->id]) . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('pagos.stripe.cancel'),
            ]);
        } catch (\\Throwable $e) {
            Log::warning('Error creando sesión de Stripe (multa): ' . $e->getMessage());

            return back()->with('error', 'No se pudo iniciar el pago con Stripe. Verifica que las claves STRIPE_KEY/STRIPE_SECRET estén configuradas en .env e inténtalo de nuevo.');
        }

        return redirect()->away($session->url);
    }

    /** Retorno desde Stripe tras el pago de una multa. Misma verificación que stripeSuccess(). */
    public function stripeSuccessMulta(Request $request, $multaId)
    {
        $sessionId = $request->query('session_id');
        $multa = Multa::findOrFail($multaId);

        if (!$sessionId) {
            return redirect()->route('multas.index')->with('error', 'No se recibió la sesión de pago de Stripe.');
        }

        try {
            $session = $this->stripe()->checkout->sessions->retrieve($sessionId);
        } catch (\\Throwable $e) {
            Log::warning('No se pudo verificar la sesión de Stripe (multa): ' . $e->getMessage());

            return redirect()->route('multas.index')->with('error', 'No se pudo verificar el pago con Stripe. Si ya pagaste, contacta a administración.');
        }

        if ($session->payment_status !== 'paid') {
            return redirect()->route('multas.index')->with('error', 'El pago no se completó. Si el cobro aparece en tu tarjeta, contacta a administración.');
        }

        $pago = Pago::firstOrCreate(
            ['stripe_session_id' => $sessionId],
            [
                'multa_id' => $multa->id,
                'monto_pagado' => $multa->monto,
                'fecha_pago' => now(),
                'metodo' => 'Stripe',
                'estado' => 'aprobado',
                'user_id' => auth()->id(),
            ]
        );

        if ($pago->estado !== 'aprobado') {
            $pago->update(['estado' => 'aprobado']);
        }

        $multa->estado = 'pagada';
        $multa->save();
        $this->registrarEnBitacora('Pago de multa confirmado vía Stripe', $pago->id);

        return redirect()->route('multas.index')->with('success', 'Pago de multa exitoso con Stripe.');
    }
'''

content = content[:idx_inicio] + bloque_nuevo + content[idx_fin_real:]

with open(path, "w", encoding="utf-8") as f:
    f.write(content)

print("Métodos de Stripe reescritos en PagoController (con verificación real contra la API).")
PYEOF
fi

# ----------------------------------------------------------------------------
# [6/9] PagoController: agregar soporte de multas a create()/store()
#       ("Registrar Pago" — formulario manual de admin)
# ----------------------------------------------------------------------------
echo ""
if grep -q "multas = Multa::" "$PAGO_CONTROLLER"; then
    echo "[6/9] create()/store() ya soportan multas. Sin cambios."
else
    echo "[6/9] Agregando soporte de multas a Registrar Pago (create/store)..."
    python3 - "$PAGO_CONTROLLER" <<'PYEOF'
import sys
path = sys.argv[1]
with open(path, "r", encoding="utf-8") as f:
    content = f.read()

# --- create(): cargar también multas pendientes/pagadas->no ---
old_create = '''    public function create()
    {
        $cuotas = Cuota::with('residente')
            ->where('estado', '!=', 'pagado')
            ->orderByDesc('fecha_vencimiento')
            ->get();

        return view('pagos.create', compact('cuotas'));
    }'''

new_create = '''    public function create()
    {
        $cuotas = Cuota::with('residente')
            ->where('estado', '!=', 'pagado')
            ->orderByDesc('fecha_vencimiento')
            ->get();

        $multas = Multa::with('residente', 'empleado')
            ->where('estado', '!=', 'pagada')
            ->orderByDesc('fechaEmision')
            ->get();

        return view('pagos.create', compact('cuotas', 'multas'));
    }'''

if old_create not in content:
    print("ADVERTENCIA: no se encontró create() con el texto esperado. Revisa PagoController.php manualmente.")
    sys.exit(0)

content = content.replace(old_create, new_create, 1)

# --- store(): aceptar cuota_id O multa_id, no solo cuota_id ---
old_store = '''    public function store(Request $request)
    {
        $request->validate([
            'cuota_id' => 'required|exists:cuotas,id',
            'monto_pagado' => 'required|numeric|min:1',
            'fecha_pago' => 'required|date',
            'metodo' => 'nullable|string',
            'observacion' => 'nullable|string',
        ]);

        $cuota = Cuota::findOrFail($request->cuota_id);

        // Crear el pago
        $pago = Pago::create([
            'cuota_id' => $cuota->id,
            'monto_pagado' => $request->monto_pagado,
            'fecha_pago' => $request->fecha_pago,
            'metodo' => $request->metodo,
            'observacion' => $request->observacion,
            'user_id' => auth()->id(),
        ]);

        // Si el pago se realizó después del vencimiento, se aplica la multa
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
    }'''

new_store = '''    public function store(Request $request)
    {
        $request->validate([
            'cuota_id' => 'nullable|required_without:multa_id|exists:cuotas,id',
            'multa_id' => 'nullable|required_without:cuota_id|exists:multas,id',
            'monto_pagado' => 'required|numeric|min:1',
            'fecha_pago' => 'required|date',
            'metodo' => 'nullable|string',
            'observacion' => 'nullable|string',
        ]);

        if ($request->filled('multa_id')) {
            $multa = Multa::findOrFail($request->multa_id);

            $pago = Pago::create([
                'multa_id' => $multa->id,
                'monto_pagado' => $request->monto_pagado,
                'fecha_pago' => $request->fecha_pago,
                'metodo' => $request->metodo,
                'observacion' => $request->observacion,
                'user_id' => auth()->id(),
                'estado' => 'aprobado',
            ]);

            if ($request->monto_pagado >= $multa->monto) {
                $multa->estado = 'pagada';
                $multa->save();
            }
            $this->registrarEnBitacora('Pago de multa registrado', $pago->id);

            return redirect()->route('pagos.index')->with('success', 'Pago de multa registrado exitosamente.');
        }

        $cuota = Cuota::findOrFail($request->cuota_id);

        // Crear el pago
        $pago = Pago::create([
            'cuota_id' => $cuota->id,
            'monto_pagado' => $request->monto_pagado,
            'fecha_pago' => $request->fecha_pago,
            'metodo' => $request->metodo,
            'observacion' => $request->observacion,
            'user_id' => auth()->id(),
            'estado' => 'aprobado',
        ]);

        // Si el pago se realizó después del vencimiento, se aplica la multa
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
    }'''

if old_store not in content:
    print("ADVERTENCIA: no se encontró store() con el texto esperado. Revisa PagoController.php manualmente.")
    sys.exit(0)

content = content.replace(old_store, new_store, 1)

with open(path, "w", encoding="utf-8") as f:
    f.write(content)

print("create()/store() ahora soportan registrar pagos de multas.")
PYEOF
fi

# ----------------------------------------------------------------------------
# [7/9] Vista pagos/create.blade.php: selector cuota/multa + Stripe informativo
# ----------------------------------------------------------------------------
echo ""
CREATE_VIEW="resources/views/pagos/create.blade.php"

if grep -q "tipoPago" "$CREATE_VIEW"; then
    echo "[7/9] pagos/create.blade.php ya tiene el selector de cuota/multa. Sin cambios."
else
    echo "[7/9] Actualizando vista Registrar Pago para soportar multas..."
    cat > "$CREATE_VIEW" <<'BLADEEOF'
@extends('layouts.ap')

@section('content')
<div class="container">
    <h2 class="mb-4">Registrar Pago</h2>

    @if($errors->any())
    <div class="alert alert-danger">
        <strong>¡Ups!</strong> Hay algunos errores:<br><br>
        <ul>
            @foreach($errors->all() as $e)
            <li>{{ $e }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('pagos.store') }}" method="POST" id="registrarPagoForm">
        @csrf

        {{-- Tipo de pago: cuota o multa --}}
        <div class="mb-3">
            <label class="form-label">Tipo de pago</label>
            <select id="tipoPago" class="form-select">
                <option value="cuota">Cuota</option>
                <option value="multa">Multa</option>
            </select>
        </div>

        {{-- Selección de cuota --}}
        <div class="mb-3" id="bloqueCuota">
            <label for="cuotaSelect" class="form-label">Cuota asociada</label>
            <select name="cuota_id" id="cuotaSelect" class="form-select">
                <option value="">-- Selecciona una cuota --</option>
                @foreach($cuotas as $cuota)
                <option value="{{ $cuota->id }}" data-monto="{{ $cuota->monto }}">
                    Cuota #{{ $cuota->id }} - {{ $cuota->residente->nombre_completo ?? 'Sin residente' }} (Bs {{ number_format($cuota->monto, 2) }})
                </option>
                @endforeach
            </select>
        </div>

        {{-- Selección de multa --}}
        <div class="mb-3 d-none" id="bloqueMulta">
            <label for="multaSelect" class="form-label">Multa asociada</label>
            <select name="multa_id" id="multaSelect" class="form-select">
                <option value="">-- Selecciona una multa --</option>
                @foreach($multas as $multa)
                <option value="{{ $multa->id }}" data-monto="{{ $multa->monto }}">
                    Multa #{{ $multa->id }} - {{ $multa->motivo }}
                    ({{ $multa->residente->nombre_completo ?? ($multa->empleado->nombre_completo ?? 'N/D') }})
                    (Bs {{ number_format($multa->monto, 2) }})
                </option>
                @endforeach
            </select>
        </div>

        {{-- Monto pagado (prellenado según la cuota/multa) --}}
        <div class="mb-3">
            <label class="form-label">Monto Pagado</label>
            <input type="number" step="0.01" name="monto_pagado" id="montoPagado" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Fecha de Pago</label>
            <input type="text" class="form-control" value="{{ now()->toDateString() }}" disabled>
            <input type="hidden" name="fecha_pago" value="{{ now()->toDateString() }}">
        </div>

        {{-- Método de pago --}}
        <div class="mb-3">
            <label class="form-label">Método de Pago</label>
            <select name="metodo" class="form-select" required>
                <option value="">-- Selecciona --</option>
                <option value="efectivo">Efectivo</option>
                <option value="transferencia">Transferencia</option>
                <option value="qr">QR</option>
                {{-- Nota: "Stripe" se registra automáticamente desde el flujo de pago
                     en línea (Mis Cuotas / Multas → Pagar con tarjeta), no desde aquí. --}}
            </select>
        </div>

        {{-- Observación opcional --}}
        <div class="mb-3">
            <label class="form-label">Observación (opcional)</label>
            <textarea name="observacion" class="form-control" rows="2"></textarea>
        </div>

        <button type="submit" class="btn btn-success">Registrar Pago</button>
        <a href="{{ route('pagos.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const tipoPago = document.getElementById('tipoPago');
    const bloqueCuota = document.getElementById('bloqueCuota');
    const bloqueMulta = document.getElementById('bloqueMulta');
    const cuotaSelect = document.getElementById('cuotaSelect');
    const multaSelect = document.getElementById('multaSelect');
    const montoInput = document.getElementById('montoPagado');

    function actualizarVisibilidad() {
        if (tipoPago.value === 'cuota') {
            bloqueCuota.classList.remove('d-none');
            bloqueMulta.classList.add('d-none');
            cuotaSelect.setAttribute('required', 'required');
            multaSelect.removeAttribute('required');
            multaSelect.value = '';
            montoInput.value = '';
        } else {
            bloqueMulta.classList.remove('d-none');
            bloqueCuota.classList.add('d-none');
            multaSelect.setAttribute('required', 'required');
            cuotaSelect.removeAttribute('required');
            cuotaSelect.value = '';
            montoInput.value = '';
        }
    }

    tipoPago.addEventListener('change', actualizarVisibilidad);
    actualizarVisibilidad();

    cuotaSelect.addEventListener('change', function () {
        const opt = this.options[this.selectedIndex];
        const monto = opt.getAttribute('data-monto');
        montoInput.value = monto ? parseFloat(monto).toFixed(2) : '';
    });

    multaSelect.addEventListener('change', function () {
        const opt = this.options[this.selectedIndex];
        const monto = opt.getAttribute('data-monto');
        montoInput.value = monto ? parseFloat(monto).toFixed(2) : '';
    });
});
</script>
@endsection
BLADEEOF
    echo "Vista actualizada con selector cuota/multa."
fi

# ----------------------------------------------------------------------------
# [8/9] Asistente de voz: componente Blade + controlador + ruta
#       (mapa de comandos con las URLs/rutas REALES de condominio-SA)
# ----------------------------------------------------------------------------
echo ""
VOICE_CONTROLLER="app/Http/Controllers/VoiceCommandController.php"

if [ -f "$VOICE_CONTROLLER" ]; then
    echo "[8/9] VoiceCommandController ya existe. Sin cambios."
else
    echo "[8/9] Creando asistente de voz (controlador + vista + ruta)..."

    cat > "$VOICE_CONTROLLER" <<'PHPEOF'
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class VoiceCommandController extends Controller
{
    /**
     * Asistente de voz por comandos de texto (sin IA externa): el navegador
     * transcribe la voz con la Web Speech API y este endpoint hace match del
     * texto contra el mapa de comandos de abajo para decidir a dónde navegar.
     */
    public function handle(Request $request)
    {
        $command = strtolower(trim((string) $request->input('command')));

        $commands = [
            // --- PANEL PRINCIPAL ---
            'ver inicio'            => ['url' => '/panel', 'message' => 'Volviendo al panel de control principal'],
            'ir a inicio'           => ['url' => '/panel', 'message' => 'Volviendo al panel de control principal'],
            'panel'                 => ['url' => '/panel', 'message' => 'Abriendo el panel de control'],
            'dashboard'             => ['url' => '/panel', 'message' => 'Abriendo el panel de control'],

            // --- CUOTAS, MULTAS Y PAGOS (CU7) ---
            'mis cuotas'            => ['url' => '/mis-cuotas', 'message' => 'Abriendo tus cuotas pendientes'],
            'ver cuotas'            => ['url' => '/cuotas', 'message' => 'Abriendo gestión de cuotas'],
            'cuotas'                => ['url' => '/cuotas', 'message' => 'Abriendo gestión de cuotas'],
            'tipos de cuota'        => ['url' => '/tipos-cuotas', 'message' => 'Abriendo tipos de cuota'],
            'ver multas'            => ['url' => '/multas', 'message' => 'Abriendo historial de multas'],
            'multas'                => ['url' => '/multas', 'message' => 'Abriendo historial de multas'],
            'ver pagos'             => ['url' => '/pagos', 'message' => 'Abriendo el listado de pagos'],
            'pagos'                 => ['url' => '/pagos', 'message' => 'Abriendo el listado de pagos'],
            'registrar pago'        => ['url' => '/pagos/create', 'message' => 'Abriendo formulario para registrar un pago'],

            // --- ÁREAS COMUNES Y RESERVAS (CU19) ---
            'áreas comunes'         => ['url' => '/areas-comunes', 'message' => 'Abriendo lista de áreas comunes'],
            'areas comunes'         => ['url' => '/areas-comunes', 'message' => 'Abriendo lista de áreas comunes'],
            'ver reservas'          => ['url' => '/reservas', 'message' => 'Abriendo módulo de reservas'],
            'reservas'              => ['url' => '/reservas', 'message' => 'Abriendo módulo de reservas'],

            // --- VISITAS Y GUARDIA ---
            'ver visitas'           => ['url' => '/visitas', 'message' => 'Abriendo control de visitas'],
            'visitas'               => ['url' => '/visitas', 'message' => 'Abriendo control de visitas'],
            'validar código'        => ['url' => '/validar-codigo', 'message' => 'Abriendo formulario de validación de código'],
            'validar codigo'        => ['url' => '/validar-codigo', 'message' => 'Abriendo formulario de validación de código'],
            'panel de guardia'      => ['url' => '/panel-guardia', 'message' => 'Abriendo panel de control de guardia'],

            // --- MANTENIMIENTO ---
            'mantenimientos'        => ['url' => '/mantenimientos', 'message' => 'Abriendo programación de mantenimientos'],

            // --- PERSONAL ---
            'ver cargos'            => ['url' => '/empleados/cargo', 'message' => 'Abriendo cargos de empleados'],
            'cargos'                => ['url' => '/empleados/cargo', 'message' => 'Abriendo cargos de empleados'],
            'empleados'             => ['url' => '/empleados', 'message' => 'Abriendo gestión del personal'],

            // --- ADMINISTRACIÓN Y AUDITORÍA ---
            'bitácora'              => ['url' => '/bitacora', 'message' => 'Abriendo la bitácora del sistema'],
            'bitacora'              => ['url' => '/bitacora', 'message' => 'Abriendo la bitácora del sistema'],
            'roles'                 => ['url' => '/roles', 'message' => 'Abriendo gestión de roles y permisos'],
            'usuarios'              => ['url' => '/users', 'message' => 'Abriendo lista de usuarios del sistema'],
            'residentes'            => ['url' => '/residentes', 'message' => 'Abriendo padrón de residentes'],
            'unidades'              => ['url' => '/unidades', 'message' => 'Abriendo listado de unidades'],
            'propiedades'           => ['url' => '/propiedades', 'message' => 'Abriendo listado de propiedades'],

            // --- COMUNICACIÓN (CU17/CU18/CU20) ---
            'notificaciones'        => ['url' => '/notificaciones', 'message' => 'Abriendo bandeja de notificaciones'],
            'comunicados'           => ['url' => '/comunicados', 'message' => 'Abriendo sección de comunicados'],
            'reclamos'              => ['url' => '/reclamos', 'message' => 'Abriendo gestión de reclamos'],
            'eventos'               => ['url' => '/eventos', 'message' => 'Abriendo eventos comunitarios'],
            'incidencias'           => ['url' => '/incidencias', 'message' => 'Abriendo registro de incidencias'],

            // --- INFORMES ---
            'informe administrativo' => ['url' => '/informes/administrativo', 'message' => 'Abriendo informe administrativo'],
            'informe de pagos'       => ['url' => '/informes/pagos', 'message' => 'Abriendo informe de pagos'],

            // --- EMPRESAS EXTERNAS ---
            'empresas'              => ['url' => '/empresas', 'message' => 'Abriendo empresas externas'],
        ];

        foreach ($commands as $key => $action) {
            if (str_contains($command, $key)) {
                return response()->json([
                    'success' => true,
                    'message' => $action['message'],
                    'redirect' => $action['url'],
                ]);
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'Comando no reconocido. Intenta con: "ver panel", "mis cuotas", "ver multas", "registrar pago", "ver reservas" o "ver reclamos".',
        ]);
    }
}
PHPEOF

    mkdir -p resources/views/components
    cat > resources/views/components/voice-assistant.blade.php <<'BLADEEOF'
<div class="voice-assistant-container" style="position: fixed; bottom: 30px; right: 30px; z-index: 9999999;">
    <button id="voiceBtn" type="button" style="width: 65px; height: 65px; border-radius: 50%; background: linear-gradient(135deg, #0a2b5e 0%, #1a4a8a 100%); border: 2px solid #ffffff; box-shadow: 0px 4px 15px rgba(0,0,0,0.4); cursor: pointer; display: flex; align-items: center; justify-content: center; outline: none; padding: 0;">
        <svg viewBox="0 0 24 24" style="width: 28px; height: 28px; fill: #ffffff;" xmlns="http://www.w3.org/2000/svg">
            <path d="M12 14c1.66 0 3-1.34 3-3V5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3zm5.3-3c0 3-2.54 5.1-5.3 5.1S6.7 14 6.7 11H5c0 3.41 2.72 6.23 6 6.72V21h2v-3.28c3.28-.48 6-3.3 6-6.72h-1.7z"/>
        </svg>
    </button>
</div>

<div id="voiceModal" class="modal fade" tabindex="-1" aria-hidden="true" style="z-index: 99999999;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white" style="background-color: #0a2b5e;">
                <h5 class="modal-title">🎙️ Asistente por Voz - Condominio</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <div class="mic-icon mb-3" id="micIconContainer" style="cursor: pointer; display: inline-block;">
                    <svg id="micIconSvg" viewBox="0 0 24 24" style="width: 80px; height: 80px; fill: #0a2b5e;" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 14c1.66 0 3-1.34 3-3V5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3zm5.3-3c0 3-2.54 5.1-5.3 5.1S6.7 14 6.7 11H5c0 3.41 2.72 6.23 6 6.72V21h2v-3.28c3.28-.48 6-3.3 6-6.72h-1.7z"/>
                    </svg>
                </div>
                <p id="voiceStatus" class="text-muted" style="font-weight: 500;">Haz clic en el micrófono y habla</p>
                <div id="voiceResult" class="mt-3"></div>
                <div class="alert alert-info mt-3" style="font-size: 0.85rem; text-align: left;">
                    <strong>🎯 Comandos soportados:</strong><br>
                    <span style="display:inline-block; margin-top:4px;">"ver panel", "mis cuotas", "ver multas", "registrar pago", "ver reservas", "ver visitas", "ver reclamos", "ver notificaciones", "ver bitácora".</span>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @keyframes voicePulse {
        0% { transform: scale(1); opacity: 1; }
        50% { transform: scale(1.15); fill: #dc3545; }
        100% { transform: scale(1); opacity: 1; }
    }
    .pulse-animation-svg {
        animation: voicePulse 1.5s infinite;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const voiceBtn = document.getElementById('voiceBtn');
    const voiceModalEl = document.getElementById('voiceModal');
    const voiceResult = document.getElementById('voiceResult');
    const voiceStatus = document.getElementById('voiceStatus');
    const micIconContainer = document.getElementById('micIconContainer');
    const micIconSvg = document.getElementById('micIconSvg');

    let voiceModal = null;
    let recognition = null;
    let isListening = false;

    function initModal() {
        if (typeof bootstrap !== 'undefined') {
            voiceModal = new bootstrap.Modal(voiceModalEl);
        } else {
            setTimeout(initModal, 200);
        }
    }
    initModal();

    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;

    if (!SpeechRecognition) {
        voiceBtn.disabled = true;
        voiceBtn.style.opacity = '0.6';
        voiceBtn.title = "Tu navegador no soporta reconocimiento de voz";
        return;
    }

    recognition = new SpeechRecognition();
    recognition.lang = 'es-ES';
    recognition.continuous = false;
    recognition.interimResults = false;
    recognition.maxAlternatives = 1;

    recognition.onstart = function() {
        isListening = true;
        voiceStatus.textContent = "🎤 Escuchando... habla ahora";
        micIconSvg.classList.add('pulse-animation-svg');
    };

    recognition.onend = function() {
        isListening = false;
        micIconSvg.classList.remove('pulse-animation-svg');
    };

    recognition.onresult = function(event) {
        const command = event.results[0][0].transcript.toLowerCase();
        voiceResult.innerHTML = `<p><strong>Dijiste:</strong> "${command}"</p><div class="spinner-border text-success" role="status"></div><p>Procesando...</p>`;

        fetch('{{ route("voice-command") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ command: command })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                voiceResult.innerHTML = `<div class="alert alert-success">✅ ${data.message}</div>`;
                setTimeout(() => {
                    if (voiceModal) { voiceModal.hide(); }
                    if (data.redirect) {
                        window.location.href = data.redirect;
                    }
                }, 1300);
            } else {
                voiceResult.innerHTML = `<div class="alert alert-danger">❌ ${data.message}</div>`;
                voiceStatus.textContent = "Haz clic en el micrófono para intentar de nuevo";
            }
        })
        .catch(() => {
            voiceResult.innerHTML = '<div class="alert alert-danger">Error al procesar el comando</div>';
            voiceStatus.textContent = "Haz clic en el micrófono para intentar de nuevo";
        });
    };

    recognition.onerror = function(event) {
        let errorMsg = "Error al reconocer la voz";
        switch (event.error) {
            case 'not-allowed': errorMsg = "❌ Permiso denegado. Activa el micrófono."; break;
            case 'no-speech': errorMsg = "No se detectó voz. Intenta nuevamente."; break;
            case 'network': errorMsg = "Error de red. Verifica tu conexión."; break;
            default: errorMsg = `Error: ${event.error}`;
        }
        voiceResult.innerHTML = `<div class="alert alert-warning">${errorMsg}</div>`;
        voiceStatus.textContent = "Haz clic en el micrófono para intentar de nuevo";
    };

    voiceBtn.addEventListener('click', function() {
        if (voiceModal) { voiceModal.show(); }
        voiceResult.innerHTML = '';
        voiceStatus.textContent = "Iniciando...";
    });

    voiceModalEl.addEventListener('shown.bs.modal', function() {
        voiceStatus.textContent = "Solicitando micrófono...";
        navigator.mediaDevices.getUserMedia({ audio: true })
            .then(function(stream) {
                stream.getTracks().forEach(track => track.stop());
                if (!isListening) {
                    try { recognition.start(); } catch (e) {}
                }
            })
            .catch(function() {
                voiceStatus.textContent = "❌ Permite el acceso al micrófono.";
            });
    });

    voiceModalEl.querySelectorAll('[data-bs-dismiss="modal"]').forEach(function(btn) {
        btn.addEventListener('click', function() {
            if (voiceModal) { voiceModal.hide(); }
            if (isListening && recognition) {
                try { recognition.stop(); } catch (e) {}
            }
        });
    });

    micIconContainer.addEventListener('click', function() {
        if (!isListening) {
            voiceResult.innerHTML = '';
            try { recognition.start(); } catch (e) {}
        }
    });
});
</script>
BLADEEOF

    echo "Controlador y componente de voz creados."
fi

# Ruta del asistente de voz
if grep -q "name('voice-command')" routes/web.php; then
    echo "[8/9] Ruta de voice-command ya existe en routes/web.php. Sin cambios."
else
    echo "[8/9] Agregando ruta POST /voice-command a routes/web.php..."
    python3 - routes/web.php <<'PYEOF'
import sys
path = sys.argv[1]
with open(path, "r", encoding="utf-8") as f:
    content = f.read()

use_line = "use App\\Http\\Controllers\\VoiceCommandController;\n"
if use_line.strip() not in content:
    marker_use = "use App\\Models\\Bitacora;\n"
    if marker_use in content:
        content = content.replace(marker_use, use_line + marker_use, 1)
    else:
        content = use_line + content

content += (
    "\n// ── Asistente de voz (navegación por comandos hablados) ───────────────────────\n"
    "Route::middleware(['auth'])->post('/voice-command', [VoiceCommandController::class, 'handle'])->name('voice-command');\n"
)

with open(path, "w", encoding="utf-8") as f:
    f.write(content)
print("Ruta y use agregados correctamente.")
PYEOF
fi

# Incluir el componente en el layout principal (ap.blade.php), si no está ya
LAYOUT_FILE="resources/views/layouts/ap.blade.php"
if grep -q "voice-assistant" "$LAYOUT_FILE"; then
    echo "[8/9] El layout ya incluye el asistente de voz. Sin cambios."
else
    echo "[8/9] Incluyendo <x-voice-assistant /> en $LAYOUT_FILE..."
    python3 - "$LAYOUT_FILE" <<'PYEOF'
import sys
path = sys.argv[1]
with open(path, "r", encoding="utf-8") as f:
    content = f.read()

marker = "</body>"
if marker not in content:
    print("ADVERTENCIA: no se encontró </body> en ap.blade.php. Agrega manualmente: @include('components.voice-assistant')")
    sys.exit(0)

content = content.replace(marker, "    @include('components.voice-assistant')\n\n" + marker, 1)
with open(path, "w", encoding="utf-8") as f:
    f.write(content)
print("Componente incluido en el layout.")
PYEOF
fi

# ----------------------------------------------------------------------------
# [9/9] Resumen final
# ----------------------------------------------------------------------------
echo ""
echo "== Listo =="
echo ""
echo "Pasos que debes correr tú en Codespaces para activarlo todo:"
echo ""
echo "  1. composer install"
echo "     (instala stripe/stripe-php si falta)"
echo ""
echo "  2. php artisan migrate"
echo "     (agrega stripe_session_id a la tabla 'pagos')"
echo ""
echo "  3. Edita tu .env y reemplaza:"
echo "       STRIPE_KEY=pk_test_REEMPLAZAR"
echo "       STRIPE_SECRET=sk_test_REEMPLAZAR"
echo "     con tus claves de prueba de https://dashboard.stripe.com/test/apikeys"
echo "     (esto era la causa raíz: sin esto, Stripe siempre fallaba)"
echo ""
echo "  4. php artisan config:clear && php artisan route:clear"
echo ""
echo "  5. Para probar Stripe: inicia sesión como residente, ve a Mis Cuotas"
echo "     (o Multas) → Pagar → 'Pagar con tarjeta (Stripe)', usa la tarjeta"
echo "     de prueba 4242 4242 4242 4242, cualquier fecha futura y CVC."
echo ""
echo "  6. Para probar 'Registrar Pago': ve a Cuotas y Pagos → Registrar Pago,"
echo "     elige 'Multa' en el selector de tipo y prueba el flujo completo."
echo ""
echo "  7. Para probar el asistente de voz: haz clic en el botón flotante 🎙️"
echo "     (abajo a la derecha en cualquier pantalla) y di, por ejemplo,"
echo "     'ver multas' o 'registrar pago'. Requiere HTTPS o localhost y"
echo "     permiso de micrófono (Codespaces ya sirve sobre HTTPS)."
