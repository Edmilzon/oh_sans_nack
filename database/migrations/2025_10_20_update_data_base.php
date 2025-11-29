<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('area', function (Blueprint $table) {
            $table->id('id_area');
            $table->string('nombre');
            $table->timestamps();
        });

        Schema::create('nivel', function (Blueprint $table) {
            $table->id('id_nivel');
            $table->string('nombre');
            $table->timestamps();
        });

        Schema::create('olimpiada', function (Blueprint $table) {
            $table->id('id_olimpiada');
            $table->string('nombre');
            $table->string('gestion');
            $table->timestamps();
        });

        Schema::create('grado_escolaridad', function (Blueprint $table) {
            $table->id('id_grado_escolaridad');
            $table->string('nombre');
            $table->timestamps();
        });

        Schema::create('persona',function (Blueprint $table){
            $table ->id('id_persona');
            $table ->string('nombre');
            $table ->string('apellido');
            $table ->string('ci')->unique();
            $table->enum('genero', ['M', 'F'])->nullable();
            $table->string('telefono')->nullable()->unique();
            $table->string('email')->unique();
            $table->timestamps();
        });

        Schema::create('area_olimpiada', function (Blueprint $table) {
            $table->id('id_area_olimpiada');
            $table->unsignedBigInteger('id_area');
            $table->unsignedBigInteger('id_olimpiada');
            $table->timestamps();

            $table->foreign('id_area')->references('id_area')->on('area')->onDelete('cascade');
            $table->foreign('id_olimpiada')->references('id_olimpiada')->on('olimpiada')->onDelete('cascade');
        });

        Schema::create('area_nivel', function (Blueprint $table) {
            $table->id('id_area_nivel');
            $table->unsignedBigInteger('id_area');
            $table->unsignedBigInteger('id_nivel');
            $table->unsignedBigInteger('id_grado_escolaridad')->nullable() ;
            $table->unsignedBigInteger('id_olimpiada');
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->foreign('id_area')->references('id_area')->on('area')->onDelete('cascade');
            $table->foreign('id_nivel')->references('id_nivel')->on('nivel')->onDelete('cascade');
            $table->foreign('id_olimpiada')->references('id_olimpiada')->on('olimpiada')->onDelete('cascade');
            $table->foreign('id_grado_escolaridad')->references('id_grado_escolaridad')->on('grado_escolaridad')->onDelete('cascade');
        });

        /*Schema::create('registro_nota', function (Blueprint $table) {
            $table->id('id_registro_nota');
            $table->unsignedBigInteger('id_area_nivel');
            $table->unsignedBigInteger('id_evaluadorAN');
            $table->unsignedBigInteger('id_competidor');
            $table->string('accion');
            $table->double('nota_anterior')->nullable();
            $table->double('nota_nueva')->nullable();
            $table->text('observacion')->nullable();
            $table->text('descripcion');
            $table->timestamps();

            $table->foreign('id_area_nivel')->references('id_area_nivel')->on('area_nivel')->onDelete('cascade');
            $table->foreign('id_evaluadorAN')->references('id_evaluadorAN')->on('evaluador_an')->onDelete('cascade');
            $table->foreign('id_competidor')->references('id_competidor')->on('competidor')->onDelete('cascade');
        });*/

        Schema::create('usuario', function (Blueprint $table) {
            $table->id('id_usuario');
            $table->string('nombre');
            $table->string('apellido');
            $table->string('ci')->unique();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('telefono')->nullable();
            $table->timestamps();
        });

        Schema::create('rol', function (Blueprint $table) {
            $table->id('id_rol');
            $table->string('nombre');
            $table->timestamps();
        });

        Schema::create('usuario_rol', function (Blueprint $table) {
            $table->id('id_usuario_rol');
            $table->unsignedBigInteger('id_usuario');
            $table->unsignedBigInteger('id_rol');
            $table->unsignedBigInteger('id_olimpiada');
            $table->timestamps();

            $table->foreign('id_usuario')->references('id_usuario')->on('usuario')->onDelete('cascade');
            $table->foreign('id_rol')->references('id_rol')->on('rol')->onDelete('cascade');
            $table->foreign('id_olimpiada')->references('id_olimpiada')->on('olimpiada')->onDelete('cascade');
        });

        Schema::create('institucion', function (Blueprint $table) {
            $table->id('id_institucion');
            $table->string('nombre');
            $table->timestamps();
        });

        Schema::create('parametro', function (Blueprint $table) {
            $table->id('id_parametro');
            $table->double('nota_min_clasif');
            $table->integer('cantidad_max_apro') -> nullable();
            $table->unsignedBigInteger('id_area_nivel');
            $table->timestamps();

            $table->foreign('id_area_nivel')->references('id_area_nivel')->on('area_nivel')->onDelete('cascade');
        });

        Schema::create('fase_global', function (Blueprint $table) {
            $table->id('id_fase_global');
            $table->string('codigo');
            $table->string('nombre');
            $table->integer('orden')->default(1);
            $table->timestamps();
        });

        Schema::create('accion_sistema', function (Blueprint $table) {
            $table->id('id_accion');
            $table->string('codigo')->unique();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->timestamps();
        });

        Schema::create('configuracion_accion', function (Blueprint $table) {
            $table->id('id_configuracion');
            $table->unsignedBigInteger('id_olimpiada');
            $table->unsignedBigInteger('id_fase_global');
            $table->unsignedBigInteger('id_accion');
            $table->boolean('habilitada')->default(false);
            $table->timestamps();
            
            $table->foreign('id_olimpiada')->references('id_olimpiada')->on('olimpiada')->onDelete('cascade');
            $table->foreign('id_fase_global')->references('id_fase_global')->on('fase_global')->onDelete('cascade');
            $table->foreign('id_accion')->references('id_accion')->on('accion_sistema')->onDelete('cascade');
            
            $table->unique(['id_olimpiada', 'id_fase_global', 'id_accion'], 'config_accion_unique');
        });

        Schema::create('fase', function (Blueprint $table) {
            $table->id('id_fase');
            $table->string('nombre');
            $table->integer('orden');
            $table->string('estado')->nullable();
            $table->unsignedBigInteger('id_area_nivel');
            $table->unsignedBigInteger('id_fase_global')->nullable();
            $table->timestamps();

            $table->foreign('id_area_nivel')->references('id_area_nivel')->on('area_nivel')->onDelete('cascade');
            $table->foreign('id_fase_global')->references('id_fase_global')->on('fase_global')->onDelete('cascade');
        });

        Schema::create('responsable_area', function (Blueprint $table) {
            $table->id('id_responsableArea');
            $table->unsignedBigInteger('id_usuario');
            $table->unsignedBigInteger('id_area_olimpiada');
            $table->timestamps();

            $table->foreign('id_usuario')->references('id_usuario')->on('usuario')->onDelete('cascade');
            $table->foreign('id_area_olimpiada')->references('id_area_olimpiada')->on('area_olimpiada')->onDelete('cascade');
        });

        Schema::create('evaluador_an', function (Blueprint $table) {
            $table->id('id_evaluadorAN');
            $table->unsignedBigInteger('id_usuario');
            $table->unsignedBigInteger('id_area_nivel');
            $table->timestamps();

            $table->foreign('id_usuario')->references('id_usuario')->on('usuario')->onDelete('cascade');
            $table->foreign('id_area_nivel')->references('id_area_nivel')->on('area_nivel')->onDelete('cascade');
        });

        Schema::create('archivo_csv', function (Blueprint $table) {
            $table->id('id_archivo_csv');
            $table->string('nombre');
            $table->date('fecha');
            $table->unsignedBigInteger('id_olimpiada');
            $table->timestamps();

            $table->foreign('id_olimpiada')->references('id_olimpiada')->on('olimpiada')->onDelete('cascade');
        });

        Schema::create('competencia', function (Blueprint $table) {
            $table->id('id_competencia');
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->string('estado');
            $table->unsignedBigInteger('id_responsableArea')->nullable();            
            $table->unsignedBigInteger('id_fase')->nullable();
            $table->timestamps();

            $table->foreign('id_responsableArea')->references('id_responsableArea')->on('responsable_area')->onDelete('set null');
            $table->foreign('id_fase')->references('id_fase')->on('fase')->onDelete('set null');
        });

        Schema::create('competidor', function (Blueprint $table) {
            $table->id('id_competidor');
            $table->string('departamento');
            $table->string('contacto_tutor')->nullable();
            $table->unsignedBigInteger('id_grado_escolaridad');
            $table->unsignedBigInteger('id_institucion');
            $table->unsignedBigInteger('id_area_nivel');
            $table->unsignedBigInteger('id_archivo_csv')->nullable();
            $table->unsignedBigInteger('id_persona');
            $table->timestamps();

            $table->foreign('id_institucion')->references('id_institucion')->on('institucion')->onDelete('cascade');
            $table->foreign('id_area_nivel')->references('id_area_nivel')->on('area_nivel')->onDelete('cascade');
            $table->foreign('id_archivo_csv')->references('id_archivo_csv')->on('archivo_csv')->onDelete('set null');
            $table->foreign('id_persona')->references('id_persona')->on('persona')->onDelete('cascade');
            $table->foreign('id_grado_escolaridad')->references('id_grado_escolaridad')->on('grado_escolaridad')->onDelete('cascade');
        });
        
        Schema::create('evaluacion', function (Blueprint $table) {
            $table->id('id_evaluacion');
            $table->decimal('nota');
            $table->text('observaciones')->nullable();
            $table->dateTime('fecha_evaluacion');
            $table->string('estado')->default('Pendiente');
            $table->unsignedBigInteger('id_competidor');
            $table->unsignedBigInteger('id_competencia')->nullable();
            $table->unsignedBigInteger('id_evaluadorAN')->nullable();
            $table->unsignedBigInteger('id_parametro')->nullable();
            $table->timestamps();

            $table->foreign('id_competidor')->references('id_competidor')->on('competidor')->onDelete('cascade');
            $table->foreign('id_competencia')->references('id_competencia')->on('competencia')->onDelete('set null');
            $table->foreign('id_evaluadorAN')->references('id_evaluadorAN')->on('evaluador_an')->onDelete('set null');
            $table->foreign('id_parametro')->references('id_parametro')->on('parametro')->onDelete('set null');
        });

        Schema::create('grupo', function (Blueprint $table) {
            $table->id('id_grupo');
            $table->string('nombre');
            $table->timestamps();
        });

        Schema::create('grupo_competidor', function (Blueprint $table) {
            $table->id('id_grupo_competidor');
            $table->unsignedBigInteger('id_grupo');
            $table->unsignedBigInteger('id_competidor');
            $table->timestamps();

            $table->foreign('id_grupo')->references('id_grupo')->on('grupo')->onDelete('cascade');
            $table->foreign('id_competidor')->references('id_competidor')->on('competidor')->onDelete('cascade');
        });
        // Parece que toca modificar id_competencia a id_olimpiada o considerar incluir mas id's
        Schema::create('aval', function (Blueprint $table) {
            $table->id('id_aval');
            $table->date('fecha_aval');
            $table->string('estado');
            $table->unsignedBigInteger('id_competencia');
            $table->unsignedBigInteger('id_fase');
            $table->unsignedBigInteger('id_responsableArea');
            $table->timestamps();

            $table->foreign('id_competencia')->references('id_competencia')->on('competencia')->onDelete('cascade');
            $table->foreign('id_fase')->references('id_fase')->on('fase')->onDelete('cascade');
            $table->foreign('id_responsableArea')->references('id_responsableArea')->on('responsable_area')->onDelete('cascade');
        });

        Schema::create('desclasificacion', function (Blueprint $table) {
            $table->id('id_desclasificacion');
            $table->date('fecha');
            $table->text('motivo');
            $table->unsignedBigInteger('id_competidor');
            $table->unsignedBigInteger('id_evaluacion')->nullable();
            $table->timestamps();

            $table->foreign('id_competidor')->references('id_competidor')->on('competidor')->onDelete('cascade');
            $table->foreign('id_evaluacion')->references('id_evaluacion')->on('evaluacion')->onDelete('set null');
        });

        Schema::create('medallero', function (Blueprint $table) {
            $table->id('id_medallero');
            $table->integer('puesto');
            $table->string('medalla');
            $table->unsignedBigInteger('id_competidor');
            $table->unsignedBigInteger('id_competencia');
            $table->timestamps();

            $table->foreign('id_competidor')->references('id_competidor')->on('competidor')->onDelete('cascade');
            $table->foreign('id_competencia')->references('id_competencia')->on('competencia')->onDelete('cascade');
        });
        Schema::create('departamento', function (Blueprint $table) {
            $table->id('id_departamento');
            $table->string('nombre');
              $table->timestamps();
        });

        Schema::create('param_medallero', function (Blueprint $table) {
            $table->id('id_param_medallero');
            $table->unsignedBigInteger('id_area_nivel'); 
            $table->integer('oro')->default(0);
            $table->integer('plata')->default(0);
            $table->integer('bronce')->default(0);
            $table->integer('menciones')->default(0);
            $table->timestamps();

            $table->foreign('id_area_nivel')->references('id_area_nivel')->on('area_nivel')->onDelete('cascade');
        });
    }
    
    public function down(): void
    {
        Schema::dropIfExists('medallero');
        Schema::dropIfExists('desclasificacion');
        Schema::dropIfExists('aval');
        Schema::dropIfExists('grupo_competidor');
        Schema::dropIfExists('grupo');
        Schema::dropIfExists('evaluacion');
        Schema::dropIfExists('competencia');
        Schema::dropIfExists('competidor');
        Schema::dropIfExists('archivo_csv');
        Schema::dropIfExists('evaluador_an');
        Schema::dropIfExists('responsable_area');
        Schema::dropIfExists('fase');
        Schema::dropIfExists('parametro');
        Schema::dropIfExists('institucion');
        Schema::dropIfExists('usuario_rol');
        Schema::dropIfExists('rol');
        Schema::dropIfExists('usuario');
        Schema::dropIfExists('area_nivel');
        Schema::dropIfExists('olimpiada');
        Schema::dropIfExists('nivel');
        Schema::dropIfExists('area');
        Schema::dropIfExists('area_olimpiada');
        Schema::dropIfExists('persona');
        Schema::dropIfExists('grado_escolaridad');
        Schema::dropIfExists('departamento');
        Schema::dropIfExists('param_medallero');
        Schema::dropIfExists('fase_global');
        //Schema::dropIfExists('registro_nota');
    }
};
