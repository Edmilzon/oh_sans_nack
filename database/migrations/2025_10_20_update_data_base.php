<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Lista de tablas en orden para el Schema::create.
     */
    private array $tablas = [
        'accion_sistema', 'archivo_csv', 'area', 'departamento', 'fase_global',
        'grado_escolaridad', 'grupo', 'institucion', 'nivel', 'olimpiada',
        'persona', 'personal_access_tokens', 'rol', 'usuario', 'area_olimpiada',
        'area_nivel', 'competencia', 'cronograma_fase', 'responsable_area',
        'usuario_rol', 'competidor', 'evaluador_an', 'parametro', 'param_medallero',
        'configuracion_accion', 'rol_accion', 'area_nivel_grado',
        'grupo_competidor', 'evaluacion', 'log_cambio_nota', 'medallero',
    ];

    /**
     * run the migrations.
     */
    public function up(): void
    {
        // Desactivar FK checks y eliminar tablas para una recreación limpia
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        foreach (array_reverse($this->tablas) as $tabla) {
            Schema::dropIfExists($tabla);
        }

        // ==========================================
        // 1. TABLAS BASE
        // ==========================================

        Schema::create('accion_sistema', function (Blueprint $table) {
            $table->increments('id_accion_sistema');
            $table->string('codigo', 100)->unique();
            $table->string('nombre', 250);
            $table->text('descripcion')->nullable();
            $table->timestamps();
        });

        Schema::create('archivo_csv', function (Blueprint $table) {
            $table->increments('id_archivo_csv');
            $table->string('nombre', 250);
            $table->date('fecha');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_ar')->nullable()->comment('ERROR TIPOGRÁFICO ORIGINAL REPLICADO');
        });

        Schema::create('area', function (Blueprint $table) {
            $table->increments('id_area');
            $table->string('nombre', 120);
            $table->timestamps();
        });

        Schema::create('departamento', function (Blueprint $table) {
            $table->increments('id_departamento');
            $table->string('nombre', 20);
            $table->timestamps();
        });

        Schema::create('fase_global', function (Blueprint $table) {
            $table->increments('id_fase_global');
            $table->string('codigo', 25);
            $table->string('nombre', 50);
            $table->unsignedInteger('orden');
            $table->timestamps();
        });

        Schema::create('grado_escolaridad', function (Blueprint $table) {
            $table->increments('id_grado_escolaridad');
            $table->text('nombre');
            $table->timestamps();
        });

        Schema::create('grupo', function (Blueprint $table) {
            $table->increments('id_grupo');
            $table->string('nombre', 250);
            $table->timestamps();
        });

        Schema::create('institucion', function (Blueprint $table) {
            $table->increments('id_institucion');
            $table->string('nombre', 250);
            $table->timestamps();
        });

        Schema::create('nivel', function (Blueprint $table) {
            $table->increments('id_nivel');
            $table->string('nombre', 100);
            $table->timestamps();
        });

        Schema::create('olimpiada', function (Blueprint $table) {
            $table->increments('id_olimpiada');
            $table->string('nombre', 100)->nullable();
            $table->char('gestion', 10);
            $table->boolean('estado');
            $table->timestamps();
        });

        Schema::create('persona', function (Blueprint $table) {
            $table->increments('id_persona');
            $table->string('nombre', 255);
            $table->string('apellido', 255);
            $table->char('ci', 15)->unique();
            $table->char('telefono', 15);
            $table->string('email', 255);
            $table->timestamps();
        });

        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->increments('id_personal_access_tokens');
            $table->string('tokenable_type', 255)->nullable();
            $table->string('tokenable_id', 255)->nullable();
            $table->text('name')->nullable();
            $table->string('token', 64)->nullable();
            $table->timestamp('habilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        Schema::create('rol', function (Blueprint $table) {
            $table->increments('id_rol');
            $table->string('nombre', 60);
            $table->timestamps();
        });

        // ==========================================
        // 2. TABLAS CON FK (Nivel 1)
        // ==========================================

        Schema::create('usuario', function (Blueprint $table) {
            $table->increments('id_usuario');
            $table->unsignedInteger('id_persona')->nullable();
            $table->text('email');
            $table->text('password');
            $table->timestamps();

            $table->foreign('id_persona')->references('id_persona')->on('persona')
                  ->restrictOnDelete()->restrictOnUpdate();
        });

        Schema::create('area_olimpiada', function (Blueprint $table) {
            $table->increments('id_area_olimpiada');
            $table->unsignedInteger('id_area')->nullable();
            $table->unsignedInteger('id_olimpiada')->nullable();
            $table->timestamps();

            $table->foreign('id_area')->references('id_area')->on('area')
                  ->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('id_olimpiada')->references('id_olimpiada')->on('olimpiada')
                  ->restrictOnDelete()->restrictOnUpdate();
        });

        Schema::create('area_nivel', function (Blueprint $table) {
            $table->increments('id_area_nivel');
            $table->unsignedInteger('id_area_olimpiada')->nullable();
            $table->unsignedInteger('id_nivel')->nullable();
            $table->boolean('es_activo')->nullable();
            $table->timestamps();

            $table->foreign('id_area_olimpiada')->references('id_area_olimpiada')->on('area_olimpiada')
                  ->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('id_nivel')->references('id_nivel')->on('nivel')
                  ->restrictOnDelete()->restrictOnUpdate();
        });

        Schema::create('usuario_rol', function (Blueprint $table) {
            $table->increments('id_usuario_rol');
            $table->unsignedInteger('id_usuario')->nullable();
            $table->unsignedInteger('id_rol')->nullable();
            $table->timestamps();

            $table->foreign('id_usuario')->references('id_usuario')->on('usuario')
                  ->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('id_rol')->references('id_rol')->on('rol')
                  ->restrictOnDelete()->restrictOnUpdate();
        });

        Schema::create('responsable_area', function (Blueprint $table) {
            $table->increments('id_responsable_area');
            $table->unsignedInteger('id_usuario')->nullable();
            $table->unsignedInteger('id_area_olimpiada')->nullable();
            $table->timestamps();

            $table->foreign('id_usuario')->references('id_usuario')->on('usuario')
                  ->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('id_area_olimpiada')->references('id_area_olimpiada')->on('area_olimpiada')
                  ->restrictOnDelete()->restrictOnUpdate();
        });

        Schema::create('cronograma_fase', function (Blueprint $table) {
            $table->increments('id_cronograma_fase');
            $table->unsignedInteger('id_fase_global')->nullable();
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->boolean('estado')->nullable();
            $table->timestamps();

            $table->foreign('id_fase_global')->references('id_fase_global')->on('fase_global')
                  ->restrictOnDelete()->restrictOnUpdate();
        });

        Schema::create('rol_accion', function (Blueprint $table) {
            $table->increments('id_rol_accion');
            $table->unsignedInteger('id_rol')->nullable();
            $table->unsignedInteger('id_accion_sistema')->nullable();
            $table->integer('activo')->nullable();
            $table->timestamps();

            $table->foreign('id_rol')->references('id_rol')->on('rol')
                  ->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('id_accion_sistema')->references('id_accion_sistema')->on('accion_sistema')
                  ->restrictOnDelete()->restrictOnUpdate();
        });

        Schema::create('area_nivel_grado', function (Blueprint $table) {
            $table->unsignedInteger('id_area_nivel');
            $table->unsignedInteger('id_grado_escolaridad');

            $table->primary(['id_area_nivel', 'id_grado_escolaridad']);

            $table->foreign('id_area_nivel')->references('id_area_nivel')->on('area_nivel')
                  ->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('id_grado_escolaridad')->references('id_grado_escolaridad')->on('grado_escolaridad')
                  ->restrictOnDelete()->restrictOnUpdate();
        });

        Schema::create('competencia', function (Blueprint $table) {
            $table->increments('id_competencia');
            $table->unsignedInteger('id_fase_global')->nullable();
            $table->unsignedInteger('id_area_nivel')->nullable();
            $table->unsignedInteger('id_persona')->nullable();
            $table->string('nombre_examen', 255)->nullable();
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->decimal('ponderacion', 8, 2)->nullable();
            $table->decimal('maxima_nota', 8, 2)->nullable();
            $table->boolean('es_avalado')->nullable();
            $table->boolean('estado');
            $table->timestamps();

            $table->foreign('id_fase_global')->references('id_fase_global')->on('fase_global')
                  ->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('id_area_nivel')->references('id_area_nivel')->on('area_nivel')
                  ->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('id_persona')->references('id_persona')->on('persona')
                  ->restrictOnDelete()->restrictOnUpdate();
        });

        Schema::create('competidor', function (Blueprint $table) {
            $table->increments('id_competidor');
            $table->unsignedInteger('id_archivo_csv')->nullable();
            $table->unsignedInteger('id_institucion')->nullable();
            $table->unsignedInteger('id_departamento')->nullable();
            $table->unsignedInteger('id_area_nivel')->nullable();
            $table->unsignedInteger('id_persona')->nullable();
            $table->unsignedInteger('id_grado_escolaridad')->nullable();
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

            // >>> CLAVE FORÁNEA APLICADA AQUÍ <<<
            $table->foreign('id_grado_escolaridad')->references('id_grado_escolaridad')->on('grado_escolaridad')
                  ->restrictOnDelete()->restrictOnUpdate();
            // >>> FIN DE CLAVE FORÁNEA <<<
        });

        Schema::create('evaluador_an', function (Blueprint $table) {
            $table->increments('id_evaluador_an');
            $table->unsignedInteger('id_usuario')->nullable();
            $table->unsignedInteger('id_area_nivel')->nullable();
            $table->boolean('estado');
            $table->timestamps();

            $table->foreign('id_usuario')->references('id_usuario')->on('usuario')
                  ->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('id_area_nivel')->references('id_area_nivel')->on('area_nivel')
                  ->restrictOnDelete()->restrictOnUpdate();
        });

        Schema::create('parametro', function (Blueprint $table) {
            $table->increments('id_parametro');
            $table->unsignedInteger('id_area_nivel')->nullable();
            $table->decimal('nota_min_aprobacion', 8, 2)->nullable();
            $table->integer('cantidad_maxima')->nullable();
            $table->timestamps();

            $table->foreign('id_area_nivel')->references('id_area_nivel')->on('area_nivel')
                  ->restrictOnDelete()->restrictOnUpdate();
        });

        Schema::create('param_medallero', function (Blueprint $table) {
            $table->increments('id_param_medallero');
            $table->unsignedInteger('id_area_nivel')->nullable();
            $table->integer('oro')->nullable();
            $table->integer('plata')->nullable();
            $table->integer('bronce')->nullable();
            $table->integer('mencion')->nullable();
            $table->timestamps();

            $table->foreign('id_area_nivel')->references('id_area_nivel')->on('area_nivel')
                  ->restrictOnDelete()->restrictOnUpdate();
        });

        Schema::create('configuracion_accion', function (Blueprint $table) {
            $table->increments('id_configuracion_accion');
            $table->unsignedInteger('id_area_nivel')->nullable();
            $table->unsignedInteger('id_accion_sistema')->nullable();
            $table->unsignedInteger('id_fase_global')->nullable();
            $table->boolean('habilitada');
            $table->timestamps();

            $table->foreign('id_area_nivel')->references('id_area_nivel')->on('area_nivel')
                  ->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('id_accion_sistema')->references('id_accion_sistema')->on('accion_sistema')
                  ->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('id_fase_global')->references('id_fase_global')->on('fase_global')
                  ->restrictOnDelete()->restrictOnUpdate();
        });

        Schema::create('grupo_competidor', function (Blueprint $table) {
            $table->increments('id_grupo_competidor');
            $table->unsignedInteger('id_grupo')->nullable();
            $table->unsignedInteger('id_competidor')->nullable();
            $table->timestamps();

            $table->foreign('id_grupo')->references('id_grupo')->on('grupo')
                  ->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('id_competidor')->references('id_competidor')->on('competidor')
                  ->restrictOnDelete()->restrictOnUpdate();
        });

        // ==========================================
        // 3. TABLAS CON FK (Nivel 2)
        // ==========================================

        Schema::create('evaluacion', function (Blueprint $table) {
            $table->increments('id_evaluacion');
            $table->unsignedInteger('id_competidor')->nullable();
            $table->unsignedInteger('id_competencia')->nullable();
            $table->unsignedInteger('id_evaluador_an')->nullable();
            $table->decimal('nota', 8, 2);
            $table->string('estado_competidor', 25)->nullable();
            $table->text('observacion');
            $table->timestamp('fecha');
            $table->boolean('estado');
            $table->timestamps();

            $table->foreign('id_competidor')->references('id_competidor')->on('competidor')
                  ->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('id_competencia')->references('id_competencia')->on('competencia')
                  ->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('id_evaluador_an')->references('id_evaluador_an')->on('evaluador_an')
                  ->restrictOnDelete()->restrictOnUpdate();
        });

        Schema::create('log_cambio_nota', function (Blueprint $table) {
            $table->increments('id_log_cambio_nota');
            $table->unsignedInteger('id_evaluacion')->nullable();
            $table->decimal('nota_nueva', 8, 2);
            $table->decimal('nota_anterior', 8, 2);
            $table->timestamp('fecha_cambio');

            $table->foreign('id_evaluacion')->references('id_evaluacion')->on('evaluacion')
                  ->restrictOnDelete()->restrictOnUpdate();
        });

        Schema::create('medallero', function (Blueprint $table) {
            $table->increments('id_medallero');
            $table->unsignedInteger('id_competidor')->nullable();
            $table->unsignedInteger('id_competencia')->nullable();
            $table->integer('puesto');
            $table->string('medalla', 15);
            $table->timestamps();

            $table->foreign('id_competidor')->references('id_competidor')->on('competidor')
                  ->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('id_competencia')->references('id_competencia')->on('competencia')
                  ->restrictOnDelete()->restrictOnUpdate();
        });


        // ==========================================
        // 4. TRIGGER DE AUDITORÍA (AUDITORÍA DE NOTAS)
        // ==========================================

        // Trigger para registrar cambios en la nota de evaluación
        DB::unprepared('
            DROP TRIGGER IF EXISTS trg_auditoria_notas;

            CREATE TRIGGER trg_auditoria_notas
            AFTER UPDATE ON evaluacion
            FOR EACH ROW
            BEGIN
                IF OLD.nota <> NEW.nota THEN
                    INSERT INTO log_cambio_nota (
                        id_evaluacion,
                        nota_anterior,
                        nota_nueva,
                        fecha_cambio
                    ) VALUES (
                        NEW.id_evaluacion,
                        OLD.nota,
                        NEW.nota,
                        NOW()
                    );
                END IF;
            END;
        ');


        // Reactivar FK checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    /**
     * reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Eliminar triggers antes de eliminar las tablas
        DB::unprepared('DROP TRIGGER IF EXISTS trg_auditoria_notas');

        $tablasreverse = array_reverse($this->tablas);

        foreach ($tablasreverse as $tabla) {
            Schema::dropIfExists($tabla);
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
};
