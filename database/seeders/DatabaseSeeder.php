<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Crear datos de ejemplo
        $this->call([
            PublicadorSeeder::class,
            TerritorioSeeder::class,
            RegistroSeeder::class, // Debe ir después de Publicadores y Territorios
        ]);

        // User::factory(10)->create();
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@territorios.com',
        ]);
    }
}
