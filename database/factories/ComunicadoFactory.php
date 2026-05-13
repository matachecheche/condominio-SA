<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Comunicado;

/**
 * ComunicadoFactory
 * 
 * EXPLICACIÓN: Este Factory genera datos de prueba (fake) para testing
 * Se usa cuando necesitas crear múltiples registros de comunicados para pruebas
 * Ejemplo: Comunicado::factory()->count(10)->create() crea 10 comunicados falsos
 */
class ComunicadoFactory extends Factory
{
    protected $model = Comunicado::class;

    /**
     * definition()
     * 
     * EXPLICACIÓN: Define la estructura de datos falsos que genera el Factory
     * $this->faker genera datos aleatorios pero realistas
     */
    public function definition(): array
    {
        return [
            'titulo'               => $this->faker->sentence, // Frase falsa como título
            'contenido'            => $this->faker->paragraph, // Párrafo falso como contenido
            'tipo'                 => $this->faker->randomElement(['Urgente', 'Informativo']), // Tipo aleatorio
            
            // ✅ NUEVO CAMPO: Genera destinatarios aleatorios
            // randomElement selecciona uno de los valores del array
            'destinatarios'        => $this->faker->randomElement(['Todos', 'Residentes', 'Empleados']),
            
            'usuario_id'           => 1, // Por defecto, usuario con ID 1
            'fecha_publicacion'    => $this->faker->dateTimeBetween('-1 year', 'now'), // Fecha en el último año
        ];
    }
}