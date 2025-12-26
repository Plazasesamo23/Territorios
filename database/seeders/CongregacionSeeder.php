<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Congregacion;
use App\Models\User;
use App\Models\Territorio;
use App\Models\Publicador;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class CongregacionSeeder extends Seeder
{
    public function run(): void
    {
        // Desactivar el Global Scope temporalmente
        // Usamos withoutGlobalScope para modificar registros existentes

        // 1. Crear congregaciones
        $centroSC = Congregacion::create([
            'nombre' => 'Centro - Santa Coloma',
            'codigo' => 'centro-sc',
            'ciudad' => 'Santa Coloma de Gramenet',
            'descripcion' => 'Congregación principal con territorios existentes',
            'activa' => true,
        ]);

        $sabadellEste = Congregacion::create([
            'nombre' => 'Sabadell - Este',
            'codigo' => 'sabadell-este',
            'ciudad' => 'Sabadell',
            'descripcion' => 'Nueva congregación',
            'activa' => true,
        ]);

        $this->command->info("✅ Congregaciones creadas:");
        $this->command->info("   - {$centroSC->nombre} (ID: {$centroSC->id})");
        $this->command->info("   - {$sabadellEste->nombre} (ID: {$sabadellEste->id})");

        // 2. Asignar territorios existentes a Centro - Santa Coloma
        $territoriosActualizados = DB::table('territorios')
            ->whereNull('congregacion_id')
            ->update(['congregacion_id' => $centroSC->id]);

        $this->command->info("✅ Territorios asignados a {$centroSC->nombre}: {$territoriosActualizados}");

        // 3. Asignar publicadores existentes a Centro - Santa Coloma
        $publicadoresActualizados = DB::table('publicadores')
            ->whereNull('congregacion_id')
            ->update(['congregacion_id' => $centroSC->id]);

        $this->command->info("✅ Publicadores asignados a {$centroSC->nombre}: {$publicadoresActualizados}");

        // 4. Crear usuario superadmin
        $superadmin = User::create([
            'name' => 'Super Administrador',
            'email' => 'superadmin@territorios.local',
            'password' => Hash::make('password'),
            'role' => 'superadmin',
            'congregacion_id' => null, // Superadmin no pertenece a una congregación específica
        ]);

        $this->command->info("✅ Usuario superadmin creado:");
        $this->command->info("   - Email: superadmin@territorios.local");
        $this->command->info("   - Password: password");

        // 5. Crear usuario admin para Centro - Santa Coloma
        $adminCentro = User::create([
            'name' => 'Admin Centro SC',
            'email' => 'admin.centro@territorios.local',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'congregacion_id' => $centroSC->id,
        ]);

        $this->command->info("✅ Usuario admin (Centro SC) creado:");
        $this->command->info("   - Email: admin.centro@territorios.local");
        $this->command->info("   - Password: password");

        // 6. Crear usuario admin para Sabadell - Este
        $adminSabadell = User::create([
            'name' => 'Admin Sabadell Este',
            'email' => 'admin.sabadell@territorios.local',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'congregacion_id' => $sabadellEste->id,
        ]);

        $this->command->info("✅ Usuario admin (Sabadell Este) creado:");
        $this->command->info("   - Email: admin.sabadell@territorios.local");
        $this->command->info("   - Password: password");

        $this->command->info("");
        $this->command->info("🎉 ¡Seeder completado exitosamente!");
        $this->command->info("");
        $this->command->info("📋 Resumen de usuarios:");
        $this->command->table(
            ['Email', 'Rol', 'Congregación', 'Password'],
            [
                ['superadmin@territorios.local', 'superadmin', 'Todas', 'password'],
                ['admin.centro@territorios.local', 'admin', 'Centro - Santa Coloma', 'password'],
                ['admin.sabadell@territorios.local', 'admin', 'Sabadell - Este', 'password'],
            ]
        );
    }
}
