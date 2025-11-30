<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * lista de tablas en orden para el schema::create.
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
        // desactivar fk checks para garantizar el drop exitoso (especialmente en mysql)
        db::statement('set foreign_key_checks=0;');

        // eliminar tablas existentes para una recreación limpia (similar al ddl)
        foreach (array_reverse($this->tablas) as $tabla) {
            schema::dropifexists($tabla);
        }

        // ==========================================
        // 1. creación de tablas base
        // ==========================================

        schema::create('accion_sistema', function (blueprint $table) {
            $table->increments('id_accion_sistema')->comment('');
            $table->string('codigo_acc_sis', 100)->unique()->comment('');
            $table->string('nombre_acc_sis', 250)->comment('');
            $table->text('descriocion_acc_sis')->nullable()->comment('');
            $table->timestamp('created_at')->nullable()->comment('');
            $table->timestamp('updated_at')->nullable()->comment('');
        });

        schema::create('archivo_csv', function (blueprint $table) {
            $table->increments('id_archivo_csv')->comment('');
            $table->string('nombre_arc_csv', 250)->comment('');
            $table->date('fecha_arc_csv')->comment('');
            $table->timestamp('created_at')->nullable()->comment('');
            $table->timestamp('updated_ar')->nullable()->comment('error tipográfico replicado'); // updated_ar en el ddl
        });

        schema::create('area', function (blueprint $table) {
            $table->increments('id_area')->comment('');
            $table->string('nombre_area', 120)->comment('');
            $table->timestamp('created_at')->nullable()->comment('');
            $table->timestamp('updated_at')->nullable()->comment('');
        });
        
        schema::create('departamento', function (blueprint $table) {
            $table->increments('id_departamento')->comment('');
            $table->string('nombre_dep', 20)->comment('');
            $table->timestamp('created_at')->nullable()->comment('');
            $table->timestamp('updated_at')->nullable()->comment('');
        });

        schema::create('fase_global', function (blueprint $table) {
            $table->increments('id_fase_global')->comment('');
            $table->string('codigo_fas_glo', 25)->comment('');
            $table->string('nombre_fas_glo', 50)->comment('');
            $table->unsignedinteger('orden_fas_glo')->comment(''); // numeric(8,0)
            $table->timestamp('created_at')->nullable()->comment('');
            $table->timestamp('updated_at')->nullable()->comment('');
        });

        schema::create('grado_escolaridad', function (blueprint $table) {
            $table->increments('id_grado_escolaridad')->comment('');
            $table->text('nombre_gra_esc')->comment(''); // longtext en ddl
            $table->timestamp('created_at')->nullable()->comment('');
            $table->timestamp('updated_at')->nullable()->comment('');
        });

        schema::create('grupo', function (blueprint $table) {
            $table->increments('id_grupo')->comment('');
            $table->string('nombre_grupo', 250)->comment('');
            $table->timestamp('created_at')->nullable()->comment('');
            $table->timestamp('updated_at')->nullable()->comment('');
        });

        schema::create('institucion', function (blueprint $table) {
            $table->increments('id_institucion')->comment('');
            $table->string('nombre_inst', 250)->comment('');
            $table->timestamp('created_at')->nullable()->comment('');
            $table->timestamp('updated_at')->nullable()->comment('');
        });

        schema::create('nivel', function (blueprint $table) {
            $table->increments('id_nivel')->comment('');
            $table->string('nombre_nivel', 100)->comment('');
            $table->timestamp('created_at')->nullable()->comment('');
            $table->timestamp('updated_at')->nullable()->comment('');
        });

        schema::create('olimpiada', function (blueprint $table) {
            $table->increments('id_olimpiada')->comment('');
            $table->string('nombre_olimp', 100)->nullable()->comment('');
            $table->char('gestion_olimp', 10)->comment('');
            $table->boolean('estado_olimp')->comment('');
            $table->timestamp('created_at')->nullable()->comment('');
            $table->timestamp('updated_at')->nullable()->comment('');
        });

        schema::create('persona', function (blueprint $table) {
            $table->increments('id_persona')->comment('');
            $table->string('nombre_pers', 255)->comment('');
            $table->string('apellido_pers', 255)->comment('');
            $table->char('ci_pers', 15)->unique()->comment('');
            $table->char('telefono_pers', 15)->comment('');
            $table->string('email_pers', 255)->comment('');
            $table->timestamp('created_at')->nullable()->comment('');
            $table->timestamp('updated_at')->nullable()->comment('');
        });

        schema::create('personal_access_tokens', function (blueprint $table) {
            $table->increments('id_personal_access_tokens')->comment('');
            $table->string('tokenable_type', 255)->nullable()->comment('');
            $table->string('tokenable_id', 255)->nullable()->comment('');
            $table->text('name')->nullable()->comment('');
            $table->string('token', 64)->nullable()->comment('');
            $table->timestamp('habilities')->nullable()->comment('');
            $table->timestamp('last_used_at')->nullable()->comment('');
            $table->timestamp('expires_at')->nullable()->comment('');
            $table->timestamp('created_at')->nullable()->comment('');
            $table->timestamp('updated_at')->nullable()->comment('');
        });

        schema::create('rol', function (blueprint $table) {
            $table->increments('id_rol')->comment('');
            $table->string('nombre_rol', 60)->comment('');
            $table->timestamp('created_at')->nullable()->comment('');
            $table->timestamp('updated_at')->nullable()->comment('');
        });

        // ==========================================
        // 2. tablas con fk (nivel 1)
        // ==========================================
        
        schema::create('usuario', function (blueprint $table) {
            $table->increments('id_usuario')->comment('');
            $table->unsignedinteger('id_persona')->nullable()->comment('');
            $table->text('email_usuario')->comment(''); // ddl usa text, no string
            $table->text('password_usuario')->comment(''); // ddl usa text
            $table->timestamp('created_at')->nullable()->comment('');
            $table->timestamp('updated_at')->nullable()->comment('');

            $table->foreign('id_persona')->references('id_persona')->on('persona')
                  ->restrictOnDelete()->restrictOnUpdate();
        });

        schema::create('area_olimpiada', function (blueprint $table) {
            $table->increments('id_area_olimpiada')->comment('');
            $table->unsignedinteger('id_area')->nullable()->comment('');
            $table->unsignedinteger('id_olimpiada')->nullable()->comment('');
            $table->timestamp('created_at')->nullable()->comment('');
            $table->timestamp('updated_at')->nullable()->comment('');

            $table->foreign('id_area')->references('id_area')->on('area')
                  ->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('id_olimpiada')->references('id_olimpiada')->on('olimpiada')
                  ->restrictOnDelete()->restrictOnUpdate();
        });

        schema::create('area_nivel', function (blueprint $table) {
            $table->increments('id_area_nivel')->comment('');
            $table->unsignedinteger('id_area_olimpiada')->nullable()->comment('');
            $table->unsignedinteger('id_nivel')->nullable()->comment('');
            $table->boolean('es_activo_area_nivel')->nullable()->comment('');
            $table->timestamp('created_at')->nullable()->comment('');
            $table->timestamp('updated_at')->nullable()->comment('');

            $table->foreign('id_area_olimpiada')->references('id_area_olimpiada')->on('area_olimpiada')
                  ->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('id_nivel')->references('id_nivel')->on('nivel')
                  ->restrictOnDelete()->restrictOnUpdate();
        });

        schema::create('usuario_rol', function (blueprint $table) {
            $table->increments('id_usuario_rol')->comment('');
            $table->unsignedinteger('id_usuario')->nullable()->comment('');
            $table->unsignedinteger('id_rol')->nullable()->comment('');
            $table->timestamp('created_at')->nullable()->comment('');
            $table->timestamp('updated_at')->nullable()->comment('');

            $table->foreign('id_usuario')->references('id_usuario')->on('usuario')
                  ->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('id_rol')->references('id_rol')->on('rol')
                  ->restrictOnDelete()->restrictOnUpdate();
        });

        schema::create('responsable_area', function (blueprint $table) {
            $table->increments('id_responsable_area')->comment('');
            $table->unsignedinteger('id_usuario')->nullable()->comment('');
            $table->unsignedinteger('id_area_olimpiada')->nullable()->comment('');
            $table->timestamp('created_at')->nullable()->comment('');
            $table->timestamp('updated_at')->nullable()->comment('');

            $table->foreign('id_usuario')->references('id_usuario')->on('usuario')
                  ->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('id_area_olimpiada')->references('id_area_olimpiada')->on('area_olimpiada')
                  ->restrictOnDelete()->restrictOnUpdate();
        });

        schema::create('cronograma_fase', function (blueprint $table) {
            $table->increments('id_cronograma_fase')->comment('');
            $table->unsignedinteger('id_fase_global')->nullable()->comment('');
            $table->date('fecha_ini_crono_fase')->comment('');
            $table->date('fecha_fin_crono_fase')->comment('');
            $table->boolean('estado_crono_fase')->nullable()->comment('');
            $table->timestamp('created_at')->nullable()->comment('');
            $table->timestamp('updated_at')->nullable()->comment('');

            $table->foreign('id_fase_global')->references('id_fase_global')->on('fase_global')
                  ->restrictOnDelete()->restrictOnUpdate();
        });

        schema::create('rol_accion', function (blueprint $table) {
            $table->increments('id_rol_accion')->comment('');
            $table->unsignedinteger('id_rol')->nullable()->comment('');
            $table->unsignedinteger('id_accion_sistema')->nullable()->comment('');
            $table->integer('activo_rol_acc')->nullable()->comment('');
            $table->timestamp('created_at')->nullable()->comment('');
            $table->timestamp('updated_at')->nullable()->comment('');

            $table->foreign('id_rol')->references('id_rol')->on('rol')
                  ->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('id_accion_sistema')->references('id_accion_sistema')->on('accion_sistema')
                  ->restrictOnDelete()->restrictOnUpdate();
        });

        schema::create('area_nivel_grado', function (blueprint $table) {
            $table->unsignedinteger('id_area_nivel')->comment('');
            $table->unsignedinteger('id_grado_escolaridad')->comment('');
            
            $table->primary(['id_area_nivel', 'id_grado_escolaridad']);

            $table->foreign('id_area_nivel')->references('id_area_nivel')->on('area_nivel')
                  ->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('id_grado_escolaridad')->references('id_grado_escolaridad')->on('grado_escolaridad')
                  ->restrictOnDelete()->restrictOnUpdate();
        });

        schema::create('competencia', function (blueprint $table) {
            $table->increments('id_competencia')->comment('');
            $table->unsignedinteger('id_fase_global')->nullable()->comment('');
            $table->unsignedinteger('id_area_nivel')->nullable()->comment('');
            $table->unsignedinteger('id_persona')->nullable()->comment(''); // columna extra en el ddl
            $table->string('nombre_examen', 255)->nullable()->comment('');
            $table->date('fecha_inicio')->comment(''); // ddl usa date
            $table->date('fecha_fin')->comment(''); // ddl usa date
            $table->decimal('ponderacion', 8, 2)->nullable()->comment(''); // tipo decimal genérico
            $table->decimal('maxima_nota', 8, 2)->nullable()->comment(''); // tipo decimal genérico
            $table->boolean('es_avalado')->nullable()->comment('');
            $table->boolean('estado_comp')->comment('');
            $table->timestamp('created_at')->nullable()->comment('');
            $table->timestamp('updated_at')->nullable()->comment('');

            $table->foreign('id_fase_global')->references('id_fase_global')->on('fase_global')
                  ->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('id_area_nivel')->references('id_area_nivel')->on('area_nivel')
                  ->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('id_persona')->references('id_persona')->on('persona')
                  ->restrictOnDelete()->restrictOnUpdate();
        });

        schema::create('competidor', function (blueprint $table) {
            $table->increments('id_competidor')->comment('');
            $table->unsignedinteger('id_archivo_csv')->nullable()->comment('');
            $table->unsignedinteger('id_institucion')->nullable()->comment('');
            $table->unsignedinteger('id_departamento')->nullable()->comment('');
            $table->unsignedinteger('id_area_nivel')->nullable()->comment(''); // fk a area_nivel en ddl
            $table->unsignedinteger('id_persona')->nullable()->comment(''); // fk a persona en ddl
            $table->char('contacto_tutor_compe', 15)->nullable()->comment('');
            $table->char('genero_competidor', 2)->nullable()->comment('');
            $table->timestamp('created_at')->nullable()->comment('');
            $table->timestamp('updated_at')->nullable()->comment('');

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
        });

        schema::create('evaluador_an', function (blueprint $table) {
            $table->increments('id_evaluador_an')->comment('');
            $table->unsignedinteger('id_usuario')->nullable()->comment('');
            $table->unsignedinteger('id_area_nivel')->nullable()->comment('');
            $table->boolean('estado_eva_an')->comment('');
            $table->timestamp('created_at')->nullable()->comment('');
            $table->timestamp('updated_at')->nullable()->comment('');

            $table->foreign('id_usuario')->references('id_usuario')->on('usuario')
                  ->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('id_area_nivel')->references('id_area_nivel')->on('area_nivel')
                  ->restrictOnDelete()->restrictOnUpdate();
        });
        
        schema::create('parametro', function (blueprint $table) {
            $table->increments('id_parametro')->comment('');
            $table->unsignedinteger('id_area_nivel')->nullable()->comment('');
            $table->decimal('nota_min_aprox_param', 8, 2)->nullable()->comment('');
            $table->integer('cantidad_maxi_param')->nullable()->comment('');
            $table->timestamp('created_at')->nullable()->comment('');
            $table->timestamp('updated_at')->nullable()->comment('');

            $table->foreign('id_area_nivel')->references('id_area_nivel')->on('area_nivel')
                  ->restrictOnDelete()->restrictOnUpdate();
        });
        
        schema::create('param_medallero', function (blueprint $table) {
            $table->increments('id_param_medallero')->comment('');
            $table->unsignedinteger('id_area_nivel')->nullable()->comment('');
            $table->integer('oro_pa_med')->nullable()->comment('');
            $table->integer('plata_pa_med')->nullable()->comment('');
            $table->integer('bronce_pa_med')->nullable()->comment('');
            $table->integer('mencion_pa_med')->nullable()->comment('');
            $table->timestamp('created_at')->nullable()->comment('');
            $table->timestamp('updated_at')->nullable()->comment('');

            $table->foreign('id_area_nivel')->references('id_area_nivel')->on('area_nivel')
                  ->restrictOnDelete()->restrictOnUpdate();
        });

        schema::create('configuracion_accion', function (blueprint $table) {
            $table->increments('id_configuracion_accion')->comment('');
            $table->unsignedinteger('id_area_nivel')->nullable()->comment('');
            $table->unsignedinteger('id_accion_sistema')->nullable()->comment('');
            $table->unsignedinteger('id_fase_global')->nullable()->comment('');
            $table->boolean('habilitada_conf_acc')->comment('');
            $table->timestamp('created_at')->nullable()->comment('');
            $table->timestamp('updated_at')->nullable()->comment('');

            $table->foreign('id_area_nivel')->references('id_area_nivel')->on('area_nivel')
                  ->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('id_accion_sistema')->references('id_accion_sistema')->on('accion_sistema')
                  ->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('id_fase_global')->references('id_fase_global')->on('fase_global')
                  ->restrictOnDelete()->restrictOnUpdate();
        });
        
        schema::create('grupo_competidor', function (blueprint $table) {
            $table->increments('id_grupo_competidor')->comment('');
            $table->unsignedinteger('id_grupo')->nullable()->comment('');
            $table->unsignedinteger('id_competidor')->nullable()->comment('');
            $table->timestamp('created_at')->nullable()->comment('');
            $table->timestamp('updated_at')->nullable()->comment('');

            $table->foreign('id_grupo')->references('id_grupo')->on('grupo')
                  ->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('id_competidor')->references('id_competidor')->on('competidor')
                  ->restrictOnDelete()->restrictOnUpdate();
        });

        // ==========================================
        // 3. tablas con fk (nivel 2)
        // ==========================================

        schema::create('evaluacion', function (blueprint $table) {
            $table->increments('id_evaluacion')->comment('');
            $table->unsignedinteger('id_competidor')->nullable()->comment('');
            $table->unsignedinteger('id_competencia')->nullable()->comment('');
            $table->unsignedinteger('id_evaluador_an')->nullable()->comment('');
            $table->decimal('nota_evalu', 8, 2)->comment('');
            $table->string('estado_competidor_eva', 25)->nullable()->comment('');
            $table->text('observacion_evalu')->comment('');
            $table->timestamp('fecha_evalu')->comment(''); // ddl usa timestamp
            $table->boolean('estado_evalu')->comment('');
            $table->timestamp('created_at')->nullable()->comment('');
            $table->timestamp('updated_at')->nullable()->comment('');

            $table->foreign('id_competidor')->references('id_competidor')->on('competidor')
                  ->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('id_competencia')->references('id_competencia')->on('competencia')
                  ->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('id_evaluador_an')->references('id_evaluador_an')->on('evaluador_an')
                  ->restrictOnDelete()->restrictOnUpdate();
        });

        schema::create('log_cambio_nota', function (blueprint $table) {
            $table->increments('id_log_cambio_nota')->comment('');
            $table->unsignedinteger('id_evaluacion')->nullable()->comment('');
            $table->decimal('nota_nueva', 8, 2)->comment('');
            $table->decimal('nota_anterior', 8, 2)->comment('');
            $table->timestamp('fecha_cambio')->comment('');
            
            $table->foreign('id_evaluacion')->references('id_evaluacion')->on('evaluacion')
                  ->restrictOnDelete()->restrictOnUpdate();
        });
        
        schema::create('medallero', function (blueprint $table) {
            $table->increments('id_medallero')->comment('');
            $table->unsignedinteger('id_competidor')->nullable()->comment('');
            $table->unsignedinteger('id_competencia')->nullable()->comment('');
            $table->integer('puesto_medall')->comment('');
            $table->string('medalla_medall', 15)->comment('');
            $table->timestamp('created_at')->nullable()->comment('');
            $table->timestamp('updated_at')->nullable()->comment('');

            $table->foreign('id_competidor')->references('id_competidor')->on('competidor')
                  ->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('id_competencia')->references('id_competencia')->on('competencia')
                  ->restrictOnDelete()->restrictOnUpdate();
        });


        // reactivar fk checks
        db::statement('set foreign_key_checks=1;');
    }

    /**
     * reverse the migrations.
     */
    public function down(): void
    {
        // el orden de eliminación es importante para las fks
        db::statement('set foreign_key_checks=0;');
        
        $tablasreverse = array_reverse($this->tablas);
        
        foreach ($tablasreverse as $tabla) {
            schema::dropifexists($tabla);
        }

        db::statement('set foreign_key_checks=1;');
    }
};
