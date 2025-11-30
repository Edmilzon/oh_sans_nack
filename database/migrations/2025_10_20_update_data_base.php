<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
<<<<<<< HEAD
        // 1. Desactivar FK checks para poder borrar y crear libremente
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // 2. Limpieza de tablas (Orden inverso de dependencia para evitar errores si FKs estuvieran activas)
=======
        // 1. Desactivar FK checks para limpieza total
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // 2. Lista de tablas a limpiar (Orden inverso de dependencia)
>>>>>>> 3941ec078f622a25b39feac36dc616b2346017d1
        $tablas = [
            'log_cambio_nota', 'medallero', 'param_medallero', 'evaluacion', 
            'grupo_competidor', 'grupo', 'inscripcion', 'competidor', 'competencia', 
            'cronograma_fase', 'fase_global', 'configuracion_accion', 'rol_accion', 
            'accion_sistema', 'usuario_rol', 'rol', 'responsable_area', 'evaluador_an', 
<<<<<<< HEAD
            'parametro', 'area_nivel', 'area_olimpiada', 'area', 'nivel', 
=======
            'parametro', 'nivel_grado', 'area_nivel', 'area_olimpiada', 'area', 'grado_escolaridad', 'nivel', 
>>>>>>> 3941ec078f622a25b39feac36dc616b2346017d1
            'olimpiada', 'usuario', 'persona', 'institucion', 'departamento', 'archivo_csv'
        ];

        foreach ($tablas as $tabla) {
            Schema::dropIfExists($tabla);
        }

<<<<<<< HEAD
        // 3. Creación de Tablas (Infraestructura y Sistema)
=======
        // ==========================================
        // 3. INFRAESTRUCTURA Y USUARIOS
        // ==========================================
>>>>>>> 3941ec078f622a25b39feac36dc616b2346017d1

        Schema::create('departamento', function (Blueprint $table) {
            $table->id('id_departamento');
            $table->string('nombre_dep', 50);
            $table->timestamps();
        });

        Schema::create('institucion', function (Blueprint $table) {
            $table->id('id_institucion');
            $table->string('nombre_inst', 250);
            $table->timestamps();
        });

        Schema::create('persona', function (Blueprint $table) {
            $table->id('id_persona');
            $table->string('nombre_pers', 100);
            $table->string('apellido_pers', 100);
            $table->string('ci_pers', 20)->unique();
            $table->string('telefono_pers', 20);
            $table->string('email_pers', 150);
            $table->timestamps();
        });

        Schema::create('usuario', function (Blueprint $table) {
            $table->id('id_usuario');
            $table->unsignedBigInteger('id_persona');
            $table->string('email_usuario', 150)->unique();
            $table->string('password_usuario');
            $table->timestamps();

            $table->foreign('id_persona')->references('id_persona')->on('persona')->onDelete('cascade');
        });

        Schema::create('rol', function (Blueprint $table) {
            $table->id('id_rol');
            $table->string('nombre_rol', 60);
            $table->timestamps();
        });

        Schema::create('olimpiada', function (Blueprint $table) {
            $table->id('id_olimpiada');
            $table->string('nombre_olimp', 100)->nullable();
<<<<<<< HEAD
            $table->string('gestion_olimp', 10); // Ej: "2025"
            $table->boolean('estado_olimp')->default(true);
            $table->timestamps();
        });

        Schema::create('usuario_rol', function (Blueprint $table) {
            $table->id('id_usuario_rol');
            $table->unsignedBigInteger('id_usuario');
            $table->unsignedBigInteger('id_rol');
            // Nota: Agrego id_olimpiada porque es vital para el multi-rol por gestión que discutimos
            $table->unsignedBigInteger('id_olimpiada')->nullable(); 
            $table->timestamps();

            $table->foreign('id_usuario')->references('id_usuario')->on('usuario')->onDelete('cascade');
            $table->foreign('id_rol')->references('id_rol')->on('rol')->onDelete('cascade');
            $table->foreign('id_olimpiada')->references('id_olimpiada')->on('olimpiada')->onDelete('cascade');
        });

        // 4. Estructura Académica

        Schema::create('area', function (Blueprint $table) {
            $table->id('id_area');
            $table->string('nombre_area', 120);
            $table->timestamps();
        });

        Schema::create('nivel', function (Blueprint $table) {
            $table->id('id_nivel');
            $table->string('nombre_nivel', 100);
=======
            $table->string('gestion_olimp', 10);
            $table->boolean('estado_olimp')->default(true);
            $table->timestamps();
        });

        Schema::create('usuario_rol', function (Blueprint $table) {
            $table->id('id_usuario_rol');
            $table->unsignedBigInteger('id_usuario');
            $table->unsignedBigInteger('id_rol');
            $table->unsignedBigInteger('id_olimpiada')->nullable(); 
            $table->timestamps();

            $table->foreign('id_usuario')->references('id_usuario')->on('usuario')->onDelete('cascade');
            $table->foreign('id_rol')->references('id_rol')->on('rol')->onDelete('cascade');
            $table->foreign('id_olimpiada')->references('id_olimpiada')->on('olimpiada')->onDelete('cascade');
        });

        // ==========================================
        // 4. ESTRUCTURA ACADÉMICA
        // ==========================================

        Schema::create('area', function (Blueprint $table) {
            $table->id('id_area');
            $table->string('nombre_area', 120);
            $table->timestamps();
        });

        Schema::create('nivel', function (Blueprint $table) {
            $table->id('id_nivel');
            $table->string('nombre_nivel', 100);
            $table->timestamps();
        });

        Schema::create('grado_escolaridad', function (Blueprint $table) {
            $table->id('id_grado_escolaridad');
            $table->string('nombre_grado', 100);
>>>>>>> 3941ec078f622a25b39feac36dc616b2346017d1
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
            $table->unsignedBigInteger('id_area_olimpiada');
            $table->unsignedBigInteger('id_nivel');
            $table->boolean('es_activo_area_nivel')->default(true);
            $table->timestamps();

            $table->foreign('id_area_olimpiada')->references('id_area_olimpiada')->on('area_olimpiada')->onDelete('cascade');
            $table->foreign('id_nivel')->references('id_nivel')->on('nivel')->onDelete('cascade');
<<<<<<< HEAD
        });

        // 5. Gestión de Participantes (NUEVO MODELO DE INSCRIPCIÓN)
=======
        });

        // --- CORRECCIÓN APLICADA AQUÍ: NIVEL_GRADO ---
        // Ahora vincula id_area_nivel con id_grado_escolaridad
        Schema::create('nivel_grado', function (Blueprint $table) {
            $table->id('id_nivel_grado');
            $table->unsignedBigInteger('id_area_nivel'); // <--- CORREGIDO (Antes id_nivel)
            $table->unsignedBigInteger('id_grado_escolaridad');
            $table->timestamps();

            $table->foreign('id_area_nivel')->references('id_area_nivel')->on('area_nivel')->onDelete('cascade');
            $table->foreign('id_grado_escolaridad')->references('id_grado_escolaridad')->on('grado_escolaridad')->onDelete('cascade');
        });

        // ==========================================
        // 5. GESTIÓN DE PARTICIPANTES
        // ==========================================
>>>>>>> 3941ec078f622a25b39feac36dc616b2346017d1

        Schema::create('archivo_csv', function (Blueprint $table) {
            $table->id('id_archivo_csv');
            $table->string('nombre_arc_csv', 250);
            $table->date('fecha_arc_csv');
            $table->timestamps();
        });

<<<<<<< HEAD
        // COMPETIDOR: Perfil único del estudiante
=======
>>>>>>> 3941ec078f622a25b39feac36dc616b2346017d1
        Schema::create('competidor', function (Blueprint $table) {
            $table->id('id_competidor');
            $table->unsignedBigInteger('id_persona');
            $table->unsignedBigInteger('id_institucion');
            $table->unsignedBigInteger('id_departamento');
<<<<<<< HEAD
            $table->unsignedBigInteger('id_archivo_csv')->nullable();
            $table->string('contacto_tutor_compe', 20)->nullable();
=======
            $table->unsignedBigInteger('id_grado_escolaridad'); // Grado actual del alumno
            $table->unsignedBigInteger('id_archivo_csv')->nullable();
            $table->string('contacto_tutor_compe', 100)->nullable();
>>>>>>> 3941ec078f622a25b39feac36dc616b2346017d1
            $table->string('genero_competidor', 2)->nullable();
            $table->timestamps();

            $table->foreign('id_persona')->references('id_persona')->on('persona')->onDelete('cascade');
            $table->foreign('id_institucion')->references('id_institucion')->on('institucion')->onDelete('cascade');
            $table->foreign('id_departamento')->references('id_departamento')->on('departamento')->onDelete('cascade');
<<<<<<< HEAD
            $table->foreign('id_archivo_csv')->references('id_archivo_csv')->on('archivo_csv')->onDelete('set null');
        });

        // INSCRIPCIÓN: Vinculación del estudiante a un área/nivel específico
        Schema::create('inscripcion', function (Blueprint $table) {
            $table->id('id_inscripcion');
            $table->unsignedBigInteger('id_competidor');
            $table->unsignedBigInteger('id_area_nivel');
            $table->string('codigo_inscripcion', 50)->nullable();
            $table->timestamps();

            $table->foreign('id_competidor')->references('id_competidor')->on('competidor')->onDelete('cascade');
            $table->foreign('id_area_nivel')->references('id_area_nivel')->on('area_nivel')->onDelete('cascade');
            $table->unique(['id_competidor', 'id_area_nivel'], 'unique_inscripcion');
        });

        Schema::create('grupo', function (Blueprint $table) {
            $table->id('id_grupo');
            $table->string('nombre_grupo', 250);
=======
            $table->foreign('id_grado_escolaridad')->references('id_grado_escolaridad')->on('grado_escolaridad')->onDelete('cascade');
            $table->foreign('id_archivo_csv')->references('id_archivo_csv')->on('archivo_csv')->onDelete('set null');
        });

        Schema::create('inscripcion', function (Blueprint $table) {
            $table->id('id_inscripcion');
            $table->unsignedBigInteger('id_competidor');
>>>>>>> 3941ec078f622a25b39feac36dc616b2346017d1
            $table->unsignedBigInteger('id_area_nivel');
            $table->timestamps();

            $table->foreign('id_competidor')->references('id_competidor')->on('competidor')->onDelete('cascade');
            $table->foreign('id_area_nivel')->references('id_area_nivel')->on('area_nivel')->onDelete('cascade');
            $table->unique(['id_competidor', 'id_area_nivel'], 'unique_inscripcion');
        });

<<<<<<< HEAD
        Schema::create('grupo_competidor', function (Blueprint $table) {
            $table->id('id_grupo_competidor');
            $table->unsignedBigInteger('id_grupo');
            $table->unsignedBigInteger('id_inscripcion'); // Ahora apunta a inscripción
=======
        // GRUPO: Simplificado (Sin id_area_nivel, como pediste)
        Schema::create('grupo', function (Blueprint $table) {
            $table->id('id_grupo');
            $table->string('nombre_grupo', 250);
            $table->timestamps();
        });

        Schema::create('grupo_competidor', function (Blueprint $table) {
            $table->id('id_grupo_competidor');
            $table->unsignedBigInteger('id_grupo');
            $table->unsignedBigInteger('id_inscripcion');
>>>>>>> 3941ec078f622a25b39feac36dc616b2346017d1
            $table->timestamps();

            $table->foreign('id_grupo')->references('id_grupo')->on('grupo')->onDelete('cascade');
            $table->foreign('id_inscripcion')->references('id_inscripcion')->on('inscripcion')->onDelete('cascade');
        });

<<<<<<< HEAD
        // 6. Evaluación y Competencia (Core)
=======
        // ==========================================
        // 6. EVALUACIÓN Y COMPETENCIA
        // ==========================================
>>>>>>> 3941ec078f622a25b39feac36dc616b2346017d1

        Schema::create('fase_global', function (Blueprint $table) {
            $table->id('id_fase_global');
            $table->string('codigo_fas_glo', 25);
            $table->string('nombre_fas_glo', 50);
            $table->integer('orden_fas_glo');
            $table->timestamps();
        });

        Schema::create('competencia', function (Blueprint $table) {
            $table->id('id_competencia');
            $table->unsignedBigInteger('id_fase_global');
            $table->unsignedBigInteger('id_area_nivel');
            $table->string('nombre_examen');
            $table->dateTime('fecha_inicio');
            $table->dateTime('fecha_fin');
            $table->decimal('ponderacion', 10, 2)->default(100.00);
            $table->decimal('maxima_nota', 10, 2)->default(100.00);
            $table->boolean('es_avalado')->default(false);
            $table->boolean('estado_comp')->default(true);
            $table->timestamps();

            $table->foreign('id_fase_global')->references('id_fase_global')->on('fase_global')->onDelete('cascade');
            $table->foreign('id_area_nivel')->references('id_area_nivel')->on('area_nivel')->onDelete('cascade');
        });

        Schema::create('evaluador_an', function (Blueprint $table) {
            $table->id('id_evaluador_an');
            $table->unsignedBigInteger('id_usuario');
            $table->unsignedBigInteger('id_area_nivel');
            $table->boolean('estado_eva_an')->default(true);
            $table->timestamps();

            $table->foreign('id_usuario')->references('id_usuario')->on('usuario')->onDelete('cascade');
            $table->foreign('id_area_nivel')->references('id_area_nivel')->on('area_nivel')->onDelete('cascade');
        });

        Schema::create('evaluacion', function (Blueprint $table) {
            $table->id('id_evaluacion');
<<<<<<< HEAD
            $table->unsignedBigInteger('id_inscripcion'); // Evalúa la inscripción
=======
            $table->unsignedBigInteger('id_inscripcion');
>>>>>>> 3941ec078f622a25b39feac36dc616b2346017d1
            $table->unsignedBigInteger('id_competencia');
            $table->unsignedBigInteger('id_evaluador_an');
            $table->decimal('nota_evalu', 10, 2);
            $table->string('estado_competidor_eva', 25)->default('PENDIENTE');
            $table->text('observacion_evalu')->nullable();
            $table->dateTime('fecha_evalu')->useCurrent();
            $table->boolean('estado_evalu')->default(true);
            $table->timestamps();

            $table->foreign('id_inscripcion')->references('id_inscripcion')->on('inscripcion')->onDelete('cascade');
            $table->foreign('id_competencia')->references('id_competencia')->on('competencia')->onDelete('cascade');
            $table->foreign('id_evaluador_an')->references('id_evaluador_an')->on('evaluador_an')->onDelete('cascade');
        });

<<<<<<< HEAD
        // 7. Auditoría y Resultados
=======
        // ==========================================
        // 7. AUDITORÍA Y RESULTADOS
        // ==========================================
>>>>>>> 3941ec078f622a25b39feac36dc616b2346017d1

        Schema::create('log_cambio_nota', function (Blueprint $table) {
            $table->id('id_log_cambio_nota');
            $table->unsignedBigInteger('id_evaluacion');
            $table->decimal('nota_anterior', 10, 2);
            $table->decimal('nota_nueva', 10, 2);
            $table->timestamp('fecha_cambio')->useCurrent();

            $table->foreign('id_evaluacion')->references('id_evaluacion')->on('evaluacion')->onDelete('cascade');
        });

        Schema::create('medallero', function (Blueprint $table) {
            $table->id('id_medallero');
            $table->unsignedBigInteger('id_inscripcion');
            $table->unsignedBigInteger('id_competencia');
            $table->integer('puesto_medall');
            $table->string('medalla_medall', 15);
            $table->timestamps();

            $table->foreign('id_inscripcion')->references('id_inscripcion')->on('inscripcion')->onDelete('cascade');
            $table->foreign('id_competencia')->references('id_competencia')->on('competencia')->onDelete('cascade');
        });

<<<<<<< HEAD
        // 8. Parametrización y Tablas de Seguridad (Restauradas)
=======
        // ==========================================
        // 8. PARAMETRIZACIÓN Y SEGURIDAD
        // ==========================================
>>>>>>> 3941ec078f622a25b39feac36dc616b2346017d1

        Schema::create('parametro', function (Blueprint $table) {
            $table->id('id_parametro');
            $table->unsignedBigInteger('id_area_nivel');
            $table->decimal('nota_min_aprox_param', 10, 2)->nullable();
            $table->integer('cantidad_maxi_param')->nullable();
            $table->timestamps();

            $table->foreign('id_area_nivel')->references('id_area_nivel')->on('area_nivel')->onDelete('cascade');
        });

        Schema::create('param_medallero', function (Blueprint $table) {
            $table->id('id_param_medallero');
            $table->unsignedBigInteger('id_area_nivel');
            $table->integer('oro_pa_med')->default(1);
            $table->integer('plata_pa_med')->default(1);
            $table->integer('bronce_pa_med')->default(1);
            $table->integer('mencion_pa_med')->default(0);
            $table->timestamps();

            $table->foreign('id_area_nivel')->references('id_area_nivel')->on('area_nivel')->onDelete('cascade');
        });

        Schema::create('accion_sistema', function (Blueprint $table) {
            $table->id('id_accion');
            $table->string('codigo_acc_sis', 100)->unique();
            $table->string('nombre_acc_sis', 250);
            $table->text('descripcion_acc_sis')->nullable();
            $table->timestamps();
        });

        Schema::create('responsable_area', function (Blueprint $table) {
            $table->id('id_responsable_area');
            $table->unsignedBigInteger('id_usuario');
            $table->unsignedBigInteger('id_area_olimpiada');
            $table->timestamps();

            $table->foreign('id_usuario')->references('id_usuario')->on('usuario')->onDelete('cascade');
            $table->foreign('id_area_olimpiada')->references('id_area_olimpiada')->on('area_olimpiada')->onDelete('cascade');
        });

<<<<<<< HEAD
        // --- TABLAS NECESARIAS PARA LA LÓGICA DE PERMISOS (Que no estaban en el SQL pero son vitales) ---
        
=======
>>>>>>> 3941ec078f622a25b39feac36dc616b2346017d1
        Schema::create('rol_accion', function (Blueprint $table) {
            $table->id('id_rol_accion');
            $table->unsignedBigInteger('id_rol');
            $table->unsignedBigInteger('id_accion');
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->foreign('id_rol')->references('id_rol')->on('rol')->onDelete('cascade');
            $table->foreign('id_accion')->references('id_accion')->on('accion_sistema')->onDelete('cascade');
            $table->unique(['id_rol', 'id_accion']);
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
            $table->unique(['id_olimpiada', 'id_fase_global', 'id_accion'], 'config_acc_unique');
        });

        Schema::create('cronograma_fase', function (Blueprint $table) {
            $table->id('id_cronograma');
            $table->unsignedBigInteger('id_olimpiada');
            $table->unsignedBigInteger('id_fase_global');
            $table->dateTime('fecha_inicio');
            $table->dateTime('fecha_fin');
            $table->enum('estado', ['Pendiente', 'En Curso', 'Finalizada'])->default('Pendiente');
            $table->timestamps();

            $table->foreign('id_olimpiada')->references('id_olimpiada')->on('olimpiada')->onDelete('cascade');
            $table->foreign('id_fase_global')->references('id_fase_global')->on('fase_global')->onDelete('cascade');
            $table->unique(['id_olimpiada', 'id_fase_global'], 'unique_crono_gestion');
        });

<<<<<<< HEAD
        // 9. Creación del Trigger (Requiere permisos de SuperUser en BD)
=======
        // 9. Trigger de Auditoría
>>>>>>> 3941ec078f622a25b39feac36dc616b2346017d1
        DB::unprepared('
            DROP TRIGGER IF EXISTS trg_auditoria_notas;
            CREATE TRIGGER trg_auditoria_notas
            AFTER UPDATE ON evaluacion
            FOR EACH ROW
            BEGIN
                IF OLD.nota_evalu <> NEW.nota_evalu THEN
                    INSERT INTO log_cambio_nota (
                        id_evaluacion,
                        nota_anterior,
                        nota_nueva,
                        fecha_cambio
                    ) VALUES (
                        OLD.id_evaluacion,
                        OLD.nota_evalu,
                        NEW.nota_evalu,
                        NOW()
                    );
                END IF;
            END
        ');

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    public function down(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        DB::unprepared('DROP TRIGGER IF EXISTS trg_auditoria_notas');

        $tablas = [
            'cronograma_fase', 'rol_accion', 'configuracion_accion', 'log_cambio_nota', 'medallero', 
            'param_medallero', 'evaluacion', 'grupo_competidor', 'grupo', 'inscripcion', 'competidor', 
            'competencia', 'fase_global', 'accion_sistema', 'usuario_rol', 'rol', 'responsable_area', 
<<<<<<< HEAD
            'evaluador_an', 'parametro', 'area_nivel', 'area_olimpiada', 'area', 'nivel', 
=======
            'evaluador_an', 'parametro', 'nivel_grado', 'area_nivel', 'area_olimpiada', 'area', 'grado_escolaridad', 'nivel', 
>>>>>>> 3941ec078f622a25b39feac36dc616b2346017d1
            'olimpiada', 'usuario', 'persona', 'institucion', 'departamento', 'archivo_csv'
        ];

        foreach ($tablas as $tabla) {
            Schema::dropIfExists($tabla);
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
};