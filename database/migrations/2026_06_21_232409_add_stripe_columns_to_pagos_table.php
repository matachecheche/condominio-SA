<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * CU7 — agrega las columnas necesarias para Stripe Checkout sin tocar ni
 * modificar las columnas existentes de la tabla `pagos` (QR/manual siguen
 * funcionando exactamente igual).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            if (!Schema::hasColumn('pagos', 'stripe_session_id')) {
                $table->string('stripe_session_id')->nullable()->unique()->after('comprobante');
            }
            if (!Schema::hasColumn('pagos', 'stripe_payment_intent_id')) {
                $table->string('stripe_payment_intent_id')->nullable()->after('stripe_session_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            if (Schema::hasColumn('pagos', 'stripe_payment_intent_id')) {
                $table->dropColumn('stripe_payment_intent_id');
            }
            if (Schema::hasColumn('pagos', 'stripe_session_id')) {
                $table->dropColumn('stripe_session_id');
            }
        });
    }
};
