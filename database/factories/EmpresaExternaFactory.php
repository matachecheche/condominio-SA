<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * EmpresaExternaFactory
 * 
 * EXPLICACIÓN GENERAL:
 * Una Factory genera datos fake (falsos) para testing
 * Se usa cuando necesitas crear múltiples registros para pruebas
 * 
 * Ejemplo de uso:
 * EmpresaExterna::factory()->count(10)->create()  // Crea 10 empresas
 * EmpresaExterna::factory()->create()             // Crea 1 empresa
 */
class EmpresaExternaFactory extends Factory
{
    /**
     * definition()
     * 
     * EXPLICACIÓN:
     * Define la estructura de datos falsos que genera el Factory
     * $this->faker genera datos aleatorios pero realistas
     */
    public function definition(): array
    {
        // Array con tipos de servicios posibles
        $servicios = [
            'Seguridad', 
            'Limpieza', 
            'Jardinería', 
            'Mantenimiento', 
            'Electricidad', 
            'Internet', 
            'Cámaras de vigilancia',
            'Plomería',
            'Pintura',
            'Gasfitería'
        ];

        return [
            'nombre'        => $this->faker->unique()->company, // Nombre de empresa único
            'servicio'      => $this->faker->randomElement($servicios), // Servicio aleatorio del array
            'telefono'      => $this->faker->numerify('7#######'), // Número simulando teléfono boliviano
            'correo'        => $this->faker->companyEmail, // Email de empresa
            'direccion'     => $this->faker->streetAddress . ', ' . $this->faker->city, // Dirección simulada
            
            // ✅ NUEVO CAMPO: Generar calificación aleatoria
            // randomElement selecciona un valor del array
            'calificacion'  => $this->faker->randomElement([1, 2, 3, 4, 5]),
            
            'observacion'   => $this->faker->optional()->realText(60), // Texto opcional
        ];
    }
}
