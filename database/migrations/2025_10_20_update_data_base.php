<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $tablas = [
        'accion_sistema', 'archivo_csv', 'area', 'departamento', 'fase_global',
        'grado_escolaridad', 'grupo', 'institucion', 'nivel', 'olimpiada', 'persona',
        'personal_access_tokens', 'rol', 'usuario', 'area_olimpiada', 'area_nivel',
        'competencia', 'cronograma_fase', 'responsable_area', 'usuario_rol', 'descalificacion_administrativa',
        'competidor', 'evaluador_an', 'parametro', 'param_medallero',
        'configuracion_accion', 'rol_accion', 'area_nivel_grado', 'grupo_competidor',
        'examen_conf', 'evaluacion', 'log_cambio_nota', 'medallero',
    ];

    public function up(): void
    {
        // ADVERTENCIA: Esta migración es destructiva.
        // Si solo quieres reiniciar la base de datos, es mejor usar:
        // php artisan migrate:fresh
        $this->down();

        // ==========================================
        // 1. TABLAS BASE
        // ==========================================

        Schema::create('accion_sistema', function (Blueprint $table) {
            $table->id('id_accion_sistema');
            $table->string('codigo', 100)->unique();
            $table->string('nombre', 250);
            $table->text('descripcion')->nullable();
            $table->timestamps();
        });

        Schema::create('archivo_csv', function (Blueprint $table) {
            $table->id('id_archivo_csv');
            $table->string('nombre', 250);
            $table->date('fecha');
            $table->timestamps();
        });

        Schema::create('area', function (Blueprint $table) {
            $table->id('id_area');
            $table->string('nombre', 120);
            $table->timestamps();
        });

        Schema::create('departamento', function (Blueprint $table) {
            $table->id('id_departamento');
            $table->string('nombre', 20);
            $table->timestamps();
        });

        Schema::create('olimpiada', function (Blueprint $table) {
            $table->id('id_olimpiada');
            $table->string('nombre', 100)->nullable();
            $table->char('gestion', 10);
            $table->boolean('estado');
            $table->timestamps();
        });

        Schema::create('fase_global', function (Blueprint $table) {
            $table->id('id_fase_global');
            $table->unsignedBigInteger('id_olimpiada')->nullable();
            $table->string('codigo', 25);
            $table->string('nombre', 50);
            $table->unsignedInteger('orden');
            $table->timestamps();

            $table->foreign('id_olimpiada')->references('id_olimpiada')->on('olimpiada')
                  ->restrictOnDelete()->restrictOnUpdate();
        });

        Schema::create('grado_escolaridad', function (Blueprint $table) {
            $table->id('id_grado_escolaridad');
            $table->text('nombre');
            $table->timestamps();
        });

        Schema::create('grupo', function (Blueprint $table) {
            $table->id('id_grupo');
            $table->string('nombre', 250);
            $table->timestamps();
        });

        Schema::create('institucion', function (Blueprint $table) {
            $table->id('id_institucion');
            $table->string('nombre', 250);
            $table->timestamps();
        });

        Schema::create('nivel', function (Blueprint $table) {
            $table->id('id_nivel');
            $table->string('nombre', 100);
            $table->timestamps();
        });

        Schema::create('persona', function (Blueprint $table) {
            $table->id('id_persona');
            $table->string('nombre', 255);
            $table->string('apellido', 255);
            $table->char('ci', 15)->unique();
            $table->char('telefono', 15);
            $table->string('email', 255);
            $table->timestamps();
        });

        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id('id_personal_access_tokens');
            $table->string('tokenable_type', 255)->nullable();
            $table->string('tokenable_id', 255)->nullable();
            $table->text('name')->nullable();
            $table->string('token', 64)->nullable();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        Schema::create('rol', function (Blueprint $table) {
            $table->id('id_rol');
            $table->string('nombre', 60);
            $table->timestamps();
        });

        // ==========================================
        // 2. TABLAS CON FK (Nivel 1) - Todas las FKs pasan a unsignedBigInteger
        // ==========================================

        Schema::create('usuario', function (Blueprint $table) {
            $table->id('id_usuario');
            $table->unsignedBigInteger('id_persona')->nullable();
            $table->string('email')->unique();
            $table->string('password');
            $table->timestamps();

            $table->foreign('id_persona')->references('id_persona')->on('persona')
                  ->restrictOnDelete()->restrictOnUpdate();
        });

        Schema::create('area_olimpiada', function (Blueprint $table) {
            $table->id('id_area_olimpiada');
            $table->unsignedBigInteger('id_area')->nullable();
            $table->unsignedBigInteger('id_olimpiada')->nullable();
            $table->timestamps();

            $table->foreign('id_area')->references('id_area')->on('area')
                  ->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('id_olimpiada')->references('id_olimpiada')->on('olimpiada')
                  ->restrictOnDelete()->restrictOnUpdate();
        });

        Schema::create('area_nivel', function (Blueprint $table) {
            $table->id('id_area_nivel');
            $table->unsignedBigInteger('id_area_olimpiada')->nullable();
            $table->unsignedBigInteger('id_nivel')->nullable();
            $table->boolean('es_activo')->nullable();
            $table->timestamps();

            $table->foreign('id_area_olimpiada')->references('id_area_olimpiada')->on('area_olimpiada')
                  ->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('id_nivel')->references('id_nivel')->on('nivel')
                  ->restrictOnDelete()->restrictOnUpdate();
        });

        Schema::create('usuario_rol', function (Blueprint $table) {
            $table->id('id_usuario_rol');
            $table->unsignedBigInteger('id_usuario')->nullable();
            $table->unsignedBigInteger('id_rol')->nullable();
            $table->unsignedBigInteger('id_olimpiada')->nullable();
            $table->timestamps();

            $table->foreign('id_usuario')->references('id_usuario')->on('usuario')
                  ->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('id_rol')->references('id_rol')->on('rol')
                  ->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('id_olimpiada')->references('id_olimpiada')->on('olimpiada')
                  ->restrictOnDelete()->restrictOnUpdate();
        });

        Schema::create('responsable_area', function (Blueprint $table) {
            $table->id('id_responsable_area');
            $table->unsignedBigInteger('id_usuario')->nullable();
            $table->unsignedBigInteger('id_area_olimpiada')->nullable();
            $table->timestamps();

            $table->foreign('id_usuario')->references('id_usuario')->on('usuario')
                  ->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('id_area_olimpiada')->references('id_area_olimpiada')->on('area_olimpiada')
                  ->restrictOnDelete()->restrictOnUpdate();
        });

        Schema::create('cronograma_fase', function (Blueprint $table) {
            $table->id('id_cronograma_fase');
            $table->unsignedBigInteger('id_fase_global')->nullable();
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->boolean('estado')->nullable();
            $table->timestamps();

            $table->foreign('id_fase_global')->references('id_fase_global')->on('fase_global')
                  ->restrictOnDelete()->restrictOnUpdate();
        });

        Schema::create('rol_accion', function (Blueprint $table) {
            $table->id('id_rol_accion');
            $table->unsignedBigInteger('id_rol')->nullable();
            $table->unsignedBigInteger('id_accion_sistema')->nullable();
            $table->integer('activo')->nullable();
            $table->timestamps();

            $table->foreign('id_rol')->references('id_rol')->on('rol')
                  ->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('id_accion_sistema')->references('id_accion_sistema')->on('accion_sistema')
                  ->restrictOnDelete()->restrictOnUpdate();
        });

        Schema::create('area_nivel_grado', function (Blueprint $table) {
            $table->unsignedBigInteger('id_area_nivel');
            $table->unsignedBigInteger('id_grado_escolaridad');

            $table->primary(['id_area_nivel', 'id_grado_escolaridad']);

            $table->foreign('id_area_nivel')->references('id_area_nivel')->on('area_nivel')
                  ->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('id_grado_escolaridad')->references('id_grado_escolaridad')->on('grado_escolaridad')
                  ->restrictOnDelete()->restrictOnUpdate();
        });

        Schema::create('competencia', function (Blueprint $table) {
            $table->id('id_competencia');
            $table->unsignedBigInteger('id_fase_global')->nullable();
            $table->unsignedBigInteger('id_area_nivel');
            $table->unsignedBigInteger('id_persona')->nullable();
            $table->string('nombre_examen', 255);
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->decimal('ponderacion', 8, 2)->nullable();
            $table->decimal('maxima_nota', 8, 2)->nullable();
            $table->string('estado', 20)->default('No_iniciada');
            $table->boolean('es_avalado')->default(false);
            $table->unsignedBigInteger('avalado_por')->nullable();
            $table->timestamp('fecha_aval')->nullable();
            $table->timestamps();

            $table->foreign('id_fase_global')
                ->references('id_fase_global')->on('fase_global')
                ->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('id_area_nivel')
                ->references('id_area_nivel')->on('area_nivel')
                ->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('id_persona')
                ->references('id_persona')->on('persona')
                ->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('avalado_por')
                ->references('id_usuario')->on('usuario')
                ->restrictOnDelete()->restrictOnUpdate();
        });

        Schema::create('competidor', function (Blueprint $table) {
            $table->id('id_competidor');
            $table->unsignedBigInteger('id_archivo_csv')->nullable();
            $table->unsignedBigInteger('id_institucion')->nullable();
            $table->unsignedBigInteger('id_departamento')->nullable();
            $table->unsignedBigInteger('id_area_nivel')->nullable();
            $table->unsignedBigInteger('id_persona')->nullable();
            $table->unsignedBigInteger('id_grado_escolaridad')->nullable();
            $table->char('contacto_tutor', 15)->nullable();
            $table->char('genero', 2)->nullable();
            $table->timestamps();

            $table->foreign('id_archivo_csv')->references('id_archivo_csv')->on('archivo_csv')
                  ->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('id_institucion')->references('id_institucion')->on('institucion')
                  ->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('id_departamento')->references('id_departamento')->on('departamento')
                  ->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('id_area_nivel')->references('id_area_nivel')->on('area_nivel')
                  ->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('id_persona')->references('id_persona')->on('persona')
                  ->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('id_grado_escolaridad')->references('id_grado_escolaridad')->on('grado_escolaridad')
                  ->restrictOnDelete()->restrictOnUpdate();
        });

        Schema::create('evaluador_an', function (Blueprint $table) {
            $table->id('id_evaluador_an');
            $table->unsignedBigInteger('id_usuario')->nullable();
            $table->unsignedBigInteger('id_area_nivel')->nullable();
            $table->boolean('estado');
            $table->timestamps();

            $table->foreign('id_usuario')->references('id_usuario')->on('usuario')
                  ->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('id_area_nivel')->references('id_area_nivel')->on('area_nivel')
                  ->restrictOnDelete()->restrictOnUpdate();
        });

        Schema::create('parametro', function (Blueprint $table) {
            $table->id('id_parametro');
            $table->unsignedBigInteger('id_area_nivel')->nullable();
            $table->decimal('nota_min_aprobacion', 8, 2)->nullable();
            $table->integer('cantidad_maxima')->nullable();
            $table->timestamps();

            $table->foreign('id_area_nivel')->references('id_area_nivel')->on('area_nivel')
                  ->restrictOnDelete()->restrictOnUpdate();
        });

        Schema::create('param_medallero', function (Blueprint $table) {
            $table->id('id_param_medallero');
            $table->unsignedBigInteger('id_area_nivel')->nullable();
            $table->integer('oro')->nullable();
            $table->integer('plata')->nullable();
            $table->integer('bronce')->nullable();
            $table->integer('mencion')->nullable();
            $table->timestamps();

            $table->foreign('id_area_nivel')->references('id_area_nivel')->on('area_nivel')
                  ->restrictOnDelete()->restrictOnUpdate();
        });

        Schema::create('configuracion_accion', function (Blueprint $table) {
            $table->id('id_configuracion_accion');
            $table->unsignedBigInteger('id_accion_sistema')->nullable();
            $table->unsignedBigInteger('id_fase_global')->nullable();
            $table->boolean('habilitada');
            $table->timestamps();

            $table->foreign('id_accion_sistema')->references('id_accion_sistema')->on('accion_sistema')
                  ->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('id_fase_global')->references('id_fase_global')->on('fase_global')
                  ->restrictOnDelete()->restrictOnUpdate();
        });

        Schema::create('grupo_competidor', function (Blueprint $table) {
            $table->id('id_grupo_competidor');
            $table->unsignedBigInteger('id_grupo')->nullable();
            $table->unsignedBigInteger('id_competidor')->nullable();
            $table->timestamps();

            $table->foreign('id_grupo')->references('id_grupo')->on('grupo')
                  ->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('id_competidor')->references('id_competidor')->on('competidor')
                  ->restrictOnDelete()->restrictOnUpdate();
        });

        Schema::create('examen_conf', function (Blueprint $table) {
            $table->id('id_examen_conf');
            $table->unsignedBigInteger('id_competencia')->nullable();
            $table->string('nombre');
            $table->decimal('ponderacion', 8, 2);
            $table->decimal('maxima_nota', 8, 2);
            $table->timestamps();

            $table->foreign('id_competencia')->references('id_competencia')->on('competencia')
                  ->restrictOnDelete()->restrictOnUpdate();
        });

        Schema::create('evaluacion', function (Blueprint $table) {
            $table->id('id_evaluacion');
            $table->unsignedBigInteger('id_competidor')->nullable();
            $table->unsignedBigInteger('id_evaluador_an')->nullable();
            $table->unsignedBigInteger('id_competencia')->nullable();
            $table->decimal('nota', 8, 2);
            $table->string('estado_competidor', 25)->nullable();
            $table->text('observacion')->nullable();
            $table->timestamp('fecha');
            $table->boolean('estado');
            $table->timestamps();

            $table->foreign('id_competidor')->references('id_competidor')->on('competidor')
                  ->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('id_evaluador_an')->references('id_evaluador_an')->on('evaluador_an')
                  ->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('id_competencia')->references('id_competencia')->on('competencia')
                  ->restrictOnDelete()->restrictOnUpdate();
        });

        Schema::create('log_cambio_nota', function (Blueprint $table) {
            $table->id('id_log_cambio_nota');
            $table->unsignedBigInteger('id_evaluacion')->nullable();
            $table->decimal('nota_nueva', 8, 2);
            $table->decimal('nota_anterior', 8, 2);
            $table->timestamp('fecha_cambio');

            $table->foreign('id_evaluacion')->references('id_evaluacion')->on('evaluacion')
                  ->restrictOnDelete()->restrictOnUpdate();
        });

        Schema::create('medallero', function (Blueprint $table) {
            $table->id('id_medallero');
            $table->unsignedBigInteger('id_competidor')->nullable();
            $table->unsignedBigInteger('id_competencia')->nullable();
            $table->integer('puesto');
            $table->string('medalla', 15);
            $table->timestamps();

            $table->foreign('id_competidor')->references('id_competidor')->on('competidor')
                  ->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('id_competencia')->references('id_competencia')->on('competencia')
                  ->restrictOnDelete()->restrictOnUpdate();
        });

        Schema::create('descalificacion_administrativa', function (Blueprint $table) {
            $table->id('id_descalificacion');
            $table->unsignedBigInteger('id_competidor');
            $table->text('observaciones');
            $table->timestamp('fecha_descalificacion')->useCurrent();
            $table->timestamps();

            $table->foreign('id_competidor')
                  ->references('id_competidor')
                  ->on('competidor')
                  ->onDelete('cascade');
        });

        // Reactivamos FKs
        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        foreach (array_reverse($this->tablas) as $tabla) {
            Schema::dropIfExists($tabla);
        }
        Schema::enableForeignKeyConstraints();
    }
};
