<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesSeeder extends Seeder{
    
    public function run():void{
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('rol')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        $roles = [
<<<<<<< HEAD
        ['nombre_rol' => 'Administrador'],
        ['nombre_rol' => 'Responsable Area'],
        ['nombre_rol' => 'Evaluador'],
=======
            ['nombre_rol' => 'Administrador'],
            ['nombre_rol' => 'Responsable Area'],
            ['nombre_rol' => 'Evaluador'],
>>>>>>> 3941ec078f622a25b39feac36dc616b2346017d1
        ];
        
        DB::table('rol')->insert($roles);
    }
}