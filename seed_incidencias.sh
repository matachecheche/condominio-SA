#!/usr/bin/env bash
# =============================================================================
# SEED — Denuncias e Incidencias (CU16) — Condominio San Diego
# Crea/sobreescribe:
#   database/seeders/IncidenciaSeeder.php
#   database/factories/IncidenciaFactory.php
# Y registra el seeder en DatabaseSeeder.php si no estaba.
#
# Ejecutar desde la RAÍZ del proyecto Laravel:
#   bash seed_incidencias.sh
# =============================================================================
set -e

GREEN='\033[0;32m'; YELLOW='\033[1;33m'; CYAN='\033[0;36m'; NC='\033[0m'
info() { echo -e "${GREEN}[+]${NC} $*"; }
warn() { echo -e "${YELLOW}[!]${NC} $*"; }
step() { echo -e "${CYAN}───${NC} $*"; }

if [ ! -f artisan ]; then
  echo "ERROR: ejecuta este script desde la raíz del proyecto Laravel." >&2
  exit 1
fi

# =============================================================================
# 1. Factory — IncidenciaFactory.php
# =============================================================================
step "Creando IncidenciaFactory..."
mkdir -p database/factories

cat > database/factories/IncidenciaFactory.php << 'PHP'
<?php

namespace Database\Factories;

use App\Models\Incidencia;
use App\Models\Residente;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory para Incidencias / Denuncias del Condominio San Diego.
 * Genera datos ficticios realistas en contexto de condominio boliviano.
 */
class IncidenciaFactory extends Factory
{
    protected $model = Incidencia::class;

    // ── Catálogos de datos ficticios ──────────────────────────────────────────

    private static array $titulos = [
        // Ruidos y convivencia
        'Ruido excesivo en horario nocturno',
        'Música a alto volumen durante el fin de semana',
        'Mascotas haciendo ruido durante la noche',
        'Fiesta sin autorización en unidad habitacional',
        'Peleas verbales frecuentes en el pasillo',
        'Niños corriendo y golpeando las paredes',
        // Infraestructura y servicios
        'Fuga de agua en tubería del pasillo 2do piso',
        'Luminaria apagada en el estacionamiento',
        'Ascensor con falla intermitente',
        'Puerta de ingreso principal no cierra bien',
        'Goteras en el techo del área de lavandería',
        'Grieta visible en la pared del pasillo norte',
        'Falla en el sistema de intercomunicadores',
        'Portón vehicular no abre correctamente',
        'Humedad excesiva en la pared del sótano',
        // Seguridad
        'Persona sospechosa merodeando el edificio',
        'Vehículo desconocido estacionado en lugar reservado',
        'Cámara de seguridad sin imagen en sector A',
        'Acceso no autorizado al área de la piscina',
        'Pérdida de llave del cuarto de herramientas',
        // Limpieza y áreas comunes
        'Basura acumulada fuera de los contenedores',
        'Piscina con agua turbia sin mantenimiento',
        'Grafiti en la pared del estacionamiento',
        'Cancha de fútbol con vidrios rotos',
        'Club House en mal estado de limpieza',
        'Falta de papel y jabón en baños comunes',
        'Olores desagradables en el área de basureros',
        // Animales y mascotas
        'Mascota sin correa en área común',
        'Dueño de mascota no recoge heces en jardín',
        'Gatos en la zona de juegos infantiles',
        // Estacionamiento y vehículos
        'Vehículo mal estacionado bloqueando salida',
        'Aceite derramado en piso del estacionamiento',
        'Motocicleta estacionada en zona peatonal',
        // Administrativos
        'Cobro incorrecto en estado de cuenta',
        'No recibí comprobante de pago del mes anterior',
        'Solicitud de revisión de multa aplicada',
        'Demora en respuesta a solicitud de mantenimiento',
    ];

    private static array $descripciones = [
        'pendiente' => [
            'Los vecinos del departamento %s llevan varias semanas generando este problema sin que nadie tome acción. Solicito intervención urgente del administrador.',
            'He reportado esto informalmente por WhatsApp pero no recibí respuesta. Presento esta denuncia formal para que quede registrada y se solucione a la brevedad.',
            'El problema persiste desde hace aproximadamente %d semanas. Adjunto que ya conversé con el vecino afectado y no llegamos a un acuerdo.',
            'Esto ocurre principalmente los días %s y afecta la convivencia de varios residentes del mismo piso.',
            'Me comuniqué con el vecino involucrado sin éxito. Solicito que la administración medie en esta situación para encontrar una solución pacífica.',
            'La situación está afectando directamente la calidad de vida de mi familia, especialmente de los menores de edad que viven con nosotros.',
        ],
        'en_revision' => [
            'Ya fue revisado por el personal de mantenimiento el %s, quien indicó que se necesitan materiales adicionales para resolver el problema completamente.',
            'El técnico evaluó la situación y presentó un presupuesto que está siendo revisado por la directiva. Esperamos resolución esta semana.',
            'Se contactó al residente involucrado y se programó una reunión de mediación para el próximo %s. Se está gestionando la solución.',
            'El guardia de turno tomó nota del incidente y se revisaron las cámaras de seguridad. Se está identificando al responsable.',
            'La empresa contratada visitó las instalaciones y determinó que se requiere una intervención mayor. Se está coordinando la fecha.',
        ],
        'resuelto' => [
            'Se realizó la reparación correspondiente el %s. El problema fue solucionado satisfactoriamente por el equipo de mantenimiento del condominio.',
            'Luego de la mediación entre las partes, se llegó a un acuerdo. Ambos residentes firmaron el acta de compromiso de convivencia.',
            'El equipo de mantenimiento realizó los arreglos necesarios. Se verificó el correcto funcionamiento y se notificó al residente afectado.',
            'La empresa externa realizó los trabajos de reparación en el plazo acordado. La incidencia quedó cerrada con satisfacción del denunciante.',
            'Se aplicaron las medidas correctivas indicadas por el reglamento interno. El residente confirmó que el problema fue resuelto.',
        ],
        'cerrado' => [
            'Incidencia cerrada por el administrador luego de verificar que el problema reportado ya no existe. Se archiva el caso.',
            'Caso cerrado por falta de información adicional del denunciante. Se puede reabrir si el problema persiste.',
            'La incidencia fue cerrada al comprobar que el reporte original no contaba con suficientes evidencias para proceder.',
            'Se cerró el caso luego de que el residente confirmó que el problema se resolvió de manera espontánea.',
        ],
    ];

    private static array $respuestasAdmin = [
        'en_revision' => [
            'Recibida la denuncia. Se asignó al encargado de mantenimiento para inspección. Se le notificará el resultado a la brevedad.',
            'Se está coordinando con el vecino involucrado para programar una reunión de mediación. Por favor tenga paciencia.',
            'La situación fue evaluada. Se requiere intervención de empresa externa. Estamos gestionando el presupuesto correspondiente.',
            'Se tomó nota de la incidencia. Se revisaron las cámaras de seguridad y se está identificando a los responsables.',
            'El problema fue constatado por personal de mantenimiento. Se están adquiriendo los materiales necesarios para la reparación.',
        ],
        'resuelto' => [
            'La incidencia fue atendida y resuelta satisfactoriamente. Gracias por su reporte, nos ayuda a mejorar la convivencia.',
            'El problema fue solucionado por nuestro equipo de mantenimiento. Si presenta nuevamente el inconveniente, no dude en reportarlo.',
            'Se realizó la mediación entre las partes y se llegó a un acuerdo. El caso queda cerrado con resolución positiva.',
            'Los trabajos de reparación fueron completados. Se realizó la verificación final y el área quedó en óptimas condiciones.',
            'Se aplicaron las sanciones correspondientes según el reglamento interno del condominio. La situación está normalizada.',
        ],
        'cerrado' => [
            'La incidencia fue cerrada. Si el problema persiste, puede presentar un nuevo reporte con mayor detalle.',
            'Caso cerrado por resolución administrativa. El expediente queda archivado para futuras referencias.',
            'Se verificó in situ y no se encontró evidencia del problema reportado. El caso se cierra sin acción adicional.',
        ],
    ];

    public function definition(): array
    {
        $residenteId = Residente::inRandomOrder()->value('id') ?? 1;
        $estado      = $this->faker->randomElement(['pendiente', 'pendiente', 'en_revision', 'en_revision', 'resuelto', 'cerrado']);
        $prioridad   = $this->faker->randomElement(['baja', 'media', 'media', 'alta']);
        $titulo      = $this->faker->randomElement(self::$titulos);

        // Descripción según estado
        $descPool    = self::$descripciones[$estado] ?? self::$descripciones['pendiente'];
        $descRaw     = $this->faker->randomElement($descPool);

        // Rellenar placeholders si los hay
        $dias        = ['lunes y jueves', 'viernes y sábado', 'fines de semana', 'martes y miércoles'];
        $descripcion = sprintf(
            $descRaw,
            $this->faker->randomElement(['201', '302', '104', '403', '105']),
            $this->faker->numberBetween(2, 8),
            $this->faker->randomElement($dias)
        );

        // Respuesta y atendido_por sólo si no es pendiente
        $respuesta  = null;
        $atendidoPor = null;
        $fechaAtencion = null;
        $adminId     = User::whereHas('roles', fn($q) => $q->where('name', 'Administrador'))->value('id') ?? 1;

        if ($estado !== 'pendiente') {
            $pool       = self::$respuestasAdmin[$estado] ?? self::$respuestasAdmin['resuelto'];
            $respuesta  = $this->faker->randomElement($pool);
            $atendidoPor = $adminId;
            $fechaAtencion = $this->faker->dateTimeBetween('-3 months', 'now');
        }

        return [
            'titulo'         => $titulo,
            'descripcion'    => $descripcion,
            'estado'         => $estado,
            'prioridad'      => $prioridad,
            'respuesta_admin'=> $respuesta,
            'residente_id'   => $residenteId,
            'atendido_por'   => $atendidoPor,
            'fecha_atencion' => $fechaAtencion,
            'created_at'     => $this->faker->dateTimeBetween('-6 months', 'now'),
            'updated_at'     => now(),
        ];
    }

    // ── Estados específicos para uso en seeder ────────────────────────────────

    public function pendiente(): static
    {
        return $this->state(fn() => [
            'estado'         => 'pendiente',
            'respuesta_admin'=> null,
            'atendido_por'   => null,
            'fecha_atencion' => null,
        ]);
    }

    public function enRevision(): static
    {
        $adminId = User::whereHas('roles', fn($q) => $q->where('name', 'Administrador'))->value('id') ?? 1;
        return $this->state(fn() => [
            'estado'         => 'en_revision',
            'respuesta_admin'=> $this->faker->randomElement(self::$respuestasAdmin['en_revision']),
            'atendido_por'   => $adminId,
            'fecha_atencion' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    public function resuelto(): static
    {
        $adminId = User::whereHas('roles', fn($q) => $q->where('name', 'Administrador'))->value('id') ?? 1;
        return $this->state(fn() => [
            'estado'         => 'resuelto',
            'respuesta_admin'=> $this->faker->randomElement(self::$respuestasAdmin['resuelto']),
            'atendido_por'   => $adminId,
            'fecha_atencion' => $this->faker->dateTimeBetween('-4 months', '-1 week'),
        ]);
    }

    public function cerrado(): static
    {
        $adminId = User::whereHas('roles', fn($q) => $q->where('name', 'Administrador'))->value('id') ?? 1;
        return $this->state(fn() => [
            'estado'         => 'cerrado',
            'respuesta_admin'=> $this->faker->randomElement(self::$respuestasAdmin['cerrado']),
            'atendido_por'   => $adminId,
            'fecha_atencion' => $this->faker->dateTimeBetween('-5 months', '-2 weeks'),
        ]);
    }

    public function altaPrioridad(): static
    {
        return $this->state(fn() => ['prioridad' => 'alta']);
    }
}
PHP

# =============================================================================
# 2. Seeder — IncidenciaSeeder.php
# =============================================================================
step "Creando IncidenciaSeeder..."
mkdir -p database/seeders

cat > database/seeders/IncidenciaSeeder.php << 'PHP'
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
PHP

# =============================================================================
# 3. Registrar en DatabaseSeeder si no está
# =============================================================================
step "Registrando IncidenciaSeeder en DatabaseSeeder..."
if grep -q "IncidenciaSeeder" database/seeders/DatabaseSeeder.php; then
    warn "IncidenciaSeeder ya estaba en DatabaseSeeder. No se modifica."
else
    # Insertar antes del cierre del array del call([...])
    python3 << 'PYEOF'
import re

with open('database/seeders/DatabaseSeeder.php', 'r') as f:
    content = f.read()

# Insertar IncidenciaSeeder al final del array call([...])
old = "        ]);\n    }\n}"
new = "            \\Database\\Seeders\\IncidenciaSeeder::class,\n        ]);\n    }\n}"

if old in content:
    content = content.replace(old, new)
    with open('database/seeders/DatabaseSeeder.php', 'w') as f:
        f.write(content)
    print("IncidenciaSeeder registrado en DatabaseSeeder.")
else:
    print("ADVERTENCIA: no se pudo insertar automáticamente. Agrégalo manualmente.")
PYEOF
fi

# =============================================================================
# 4. Verificar que el modelo Incidencia tenga HasFactory
# =============================================================================
step "Verificando que Incidencia use HasFactory..."
if ! grep -q "HasFactory" app/Models/Incidencia.php; then
    sed -i 's/use Illuminate\\Database\\Eloquent\\Model;/use Illuminate\\Database\\Eloquent\\Factories\\HasFactory;\nuse Illuminate\\Database\\Eloquent\\Model;/' app/Models/Incidencia.php
    sed -i 's/class Incidencia extends Model\n{/class Incidencia extends Model\n{\n    use HasFactory;/' app/Models/Incidencia.php
    warn "Se agregó HasFactory al modelo Incidencia."
else
    echo "  HasFactory ya estaba presente."
fi

# =============================================================================
# 5. Limpiar caché
# =============================================================================
step "Limpiando caché de Laravel..."
php artisan config:clear 2>/dev/null || true
php artisan cache:clear  2>/dev/null || true

# =============================================================================
# Resumen final
# =============================================================================
echo ""
echo -e "${GREEN}╔══════════════════════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║      Seed de Incidencias (CU16) instalado correctamente      ║${NC}"
echo -e "${GREEN}╚══════════════════════════════════════════════════════════════╝${NC}"
echo ""
echo "Archivos creados / actualizados:"
echo "  ✅  database/factories/IncidenciaFactory.php"
echo "  ✅  database/seeders/IncidenciaSeeder.php"
echo "  ✅  database/seeders/DatabaseSeeder.php  (registrado)"
echo ""
echo "Para cargar los datos en la BD, ejecutá:"
echo ""
echo "  # Solo incidencias (sin borrar datos existentes):"
echo "  php artisan db:seed --class=IncidenciaSeeder"
echo ""
echo "  # O desde cero (resetea toda la BD):"
echo "  php artisan migrate:fresh --seed"
echo ""
echo "Distribución de datos que se crean:"
echo "   8  casos fijos con texto realista (para demo)"
echo "  12  pendientes"
echo "  12  en revisión  (con respuesta del admin)"
echo "  10  resueltas    (con respuesta del admin)"
echo "   6  cerradas     (con respuesta del admin)"
echo "   5  urgentes     (alta prioridad, pendientes)"
echo "  ─────────────────────────────────────────────"
echo "  53  incidencias en total"
