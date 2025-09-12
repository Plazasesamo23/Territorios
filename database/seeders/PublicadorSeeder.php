<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Publicador;

class PublicadorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $publicadores = [
            // Publicadores activos regulares
            [
                'nombre' => 'Juan Pérez',
                'telefono' => '+34 612 345 678',
                'notas' => 'Publicador experimentado, territorio urbano',
                'activo' => true
            ],
            [
                'nombre' => 'María García',
                'telefono' => '+34 623 456 789',
                'notas' => 'Prefiere territorios rurales',
                'activo' => true
            ],
            [
                'nombre' => 'Carlos López',
                'telefono' => '+34 634 567 890',
                'notas' => 'Disponible fines de semana',
                'activo' => true
            ],
            [
                'nombre' => 'Ana Martínez',
                'telefono' => '+34 645 678 901',
                'notas' => 'Territorio cerca del centro',
                'activo' => true
            ],
            [
                'nombre' => 'Pedro Sánchez',
                'telefono' => '+34 656 789 012',
                'notas' => 'Muy comprometido, cualquier territorio',
                'activo' => true
            ],
            
            // Más publicadores para pruebas
            [
                'nombre' => 'Laura Fernández',
                'telefono' => '+34 667 890 123',
                'notas' => 'Nueva publicadora, muy entusiasta',
                'activo' => true
            ],
            [
                'nombre' => 'Miguel Rodríguez',
                'telefono' => '+34 678 901 234',
                'notas' => 'Prefiere territorios pequeños',
                'activo' => true
            ],
            [
                'nombre' => 'Carmen Jiménez',
                'telefono' => '+34 689 012 345',
                'notas' => 'Muy rápida completando territorios',
                'activo' => true
            ],
            [
                'nombre' => 'Roberto Torres',
                'telefono' => '+34 690 123 456',
                'notas' => 'Disponible entre semana',
                'activo' => true
            ],
            [
                'nombre' => 'Elena Morales',
                'telefono' => '+34 601 234 567',
                'notas' => 'Experiencia con territorios difíciles',
                'activo' => true
            ],
            
            // Algunos inactivos para pruebas
            [
                'nombre' => 'José Ruiz',
                'telefono' => '+34 612 345 999',
                'notas' => 'Temporalmente inactivo por trabajo',
                'activo' => false
            ],
            [
                'nombre' => 'Isabel Vega',
                'telefono' => '+34 623 456 888',
                'notas' => 'Inactiva por mudanza',
                'activo' => false
            ],
        ];

        foreach ($publicadores as $publicador) {
            Publicador::create($publicador);
        }
    }
}
