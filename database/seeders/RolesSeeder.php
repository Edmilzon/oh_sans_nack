<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use App\Model\Rol;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        Rol::truncate();
        Schema::enableForeignKeyConstraints();

        $roles = [
            'Administrador',
            'Responsable Area',
            'Evaluador',
        ];

        $this->command->info('Creando roles del sistema...');

        foreach ($roles as $nombreRol) {
            Rol::firstOrCreate(['nombre' => $nombreRol]);
        }

        $this->command->info('✅ Roles creados exitosamente.');
    }
}
