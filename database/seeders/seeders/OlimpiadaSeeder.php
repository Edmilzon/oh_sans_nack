<?php

namespace Database\Seeders;

use App\Model\Olimpiada;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OlimpiadaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Olimpiada::create([
            'nombre' => 'Olimpiada Científica Estudiantil',
            'gestion' => date('Y'),
        ]);

        $this->command->info('Olimpiada de prueba creada exitosamente.');
    }
}
