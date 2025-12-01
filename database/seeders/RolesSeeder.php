<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use App\Model\Rol;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Limpieza segura de la tabla (evita errores de FK)
        Schema::disableForeignKeyConstraints();
        Rol::truncate();
        Schema::enableForeignKeyConstraints();

        // 2. Roles a crear
        $roles = [
            'Administrador',
            'Responsable Area',
            'Evaluador',
        ];

        $this->command->info('Creando roles del sistema...');

        // 3. Inserción usando Eloquent (maneja timestamps automáticamente)
        foreach ($roles as $nombreRol) {
            Rol::firstOrCreate(['nombre' => $nombreRol]);
        }

        $this->command->info('✅ Roles creados exitosamente.');
    }
}
