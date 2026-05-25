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
