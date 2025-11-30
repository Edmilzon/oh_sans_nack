<?php

namespace App\Repositories;

use App\Model\GradoEscolaridad;
use App\Model\Departamento;
use App\Model\Olimpiada; // Asegurar que está importado para evitar errores
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ListaResponsableAreaRepository
{
    public function getNivelesByArea(int $idArea): Collection
    {
        $gestionActual = date('Y');

        return DB::table('area_nivel')
            ->join('nivel', 'area_nivel.id_nivel', '=', 'nivel.id_nivel')
            ->join('area_olimpiada', 'area_nivel.id_area_olimpiada', '=', 'area_olimpiada.id_area_olimpiada')
            ->join('olimpiada', 'area_olimpiada.id_olimpiada', '=', 'olimpiada.id_olimpiada')
            ->select(
                'nivel.id_nivel',
                'nivel.nombre_nivel as nombre_nivel', // Columna corregida
            )
            ->where('area_olimpiada.id_area', $idArea) // Se accede a area_olimpiada para el id_area
            ->where('olimpiada.gestion_olimp', $gestionActual) // Columna corregida
            ->where('area_nivel.es_activo_area_nivel', true) // Columna corregida
            ->orderBy('nivel.nombre_nivel') // Columna corregida
            ->distinct()
            ->get();
    }

    public function getAreaPorResponsable(int $idResponsable): Collection
    {
        $gestionActual = date('Y');

        return DB::table('responsable_area')
            ->join('area_olimpiada', 'responsable_area.id_area_olimpiada', '=', 'area_olimpiada.id_area_olimpiada')
            ->join('area', 'area_olimpiada.id_area', '=', 'area.id_area')
            ->join('olimpiada', 'area_olimpiada.id_olimpiada', '=', 'olimpiada.id_olimpiada')
            // Se utiliza alias 'nombre' para compatibilidad con el frontend
            ->select('area.id_area', 'area.nombre_area as nombre') // Columna corregida
            ->where('responsable_area.id_usuario', $idResponsable)
            ->where('olimpiada.gestion_olimp', $gestionActual) // Columna corregida
            ->distinct()
            ->orderBy('area.nombre_area') // Columna corregida
            ->get();
    }

    /**
     * Lista los competidores filtrando por área/nivel/grado
     */
   public function listarPorAreaYNivel(
    int $idResponsable,
    ?int $idArea,
    ?int $idNivel,
    ?int $idGrado,
    ?string $genero = null,
    ?string $departamento = null
): Collection
{
    // Lógica para obtener las áreas de las que el responsable es responsable
    $areasDelResponsable = DB::table('responsable_area')
        ->join('area_olimpiada', 'responsable_area.id_area_olimpiada', '=', 'area_olimpiada.id_area_olimpiada')
        ->where('responsable_area.id_usuario', $idResponsable)
        ->pluck('area_olimpiada.id_area')
        ->unique()
        ->values();

    if ($areasDelResponsable->isEmpty()) {
        return collect();
    }

    // Mapeo de género (Manteniendo la lógica original)
    if ($genero && !in_array(strtolower($genero), ['m', 'f', 'masculino', 'femenino'])) {
        $departamento = $genero;
        $genero = null;
    }

    $query = DB::table('competidor')
        ->join('persona', 'competidor.id_persona', '=', 'persona.id_persona')
        // Se une a area_nivel a través de competidor
        ->join('inscripcion', 'competidor.id_competidor', '=', 'inscripcion.id_competidor')
        ->join('area_nivel', 'inscripcion.id_area_nivel', '=', 'area_nivel.id_area_nivel')
        // El resto de joins para obtener datos de la inscripción
        ->join('area_olimpiada', 'area_nivel.id_area_olimpiada', '=', 'area_olimpiada.id_area_olimpiada')
        ->join('area', 'area_olimpiada.id_area', '=', 'area.id_area')
        ->join('nivel', 'area_nivel.id_nivel', '=', 'nivel.id_nivel')
        ->join('grado_escolaridad', 'competidor.id_grado_escolaridad', '=', 'grado_escolaridad.id_grado_escolaridad')
        ->join('institucion', 'competidor.id_institucion', '=', 'institucion.id_institucion')
        ->join('olimpiada', 'area_olimpiada.id_olimpiada', '=', 'olimpiada.id_olimpiada')
        // Filtrar por áreas del responsable
        ->whereIn('area.id_area', $areasDelResponsable);

    // 🧩 Filtro por olimpiada del año actual (Columna corregida)
    $anioActual = date('Y');
    $query->where('olimpiada.gestion_olimp', $anioActual);

    // Filtros opcionales
    if ($idArea && $idArea !== 0) {
        $query->where('area.id_area', $idArea);
    }

    if ($idNivel && $idNivel !== 0) {
        $query->where('nivel.id_nivel', $idNivel);
    }

    if ($idGrado && $idGrado !== 0) {
        $query->where('grado_escolaridad.id_grado_escolaridad', $idGrado);
    }

    if ($genero) {
        $genero = strtoupper(substr($genero, 0, 1));
        $query->where('competidor.genero_competidor', $genero); // Columna corregida
    }

    if ($departamento) {
        // En competidor, la columna es 'id_departamento' (FK) o 'departamento' (texto).
        // Asumo que tu base de datos anterior guardaba el texto del departamento en la columna 'departamento' del competidor.
        // Si no existe, usar la tabla 'departamento' y un join.
        // Mantenemos la lógica original sobre competidor.departamento si existía.
        $query->whereRaw('LOWER(competidor.departamento) = ?', [strtolower($departamento)]);
    }

    // SELECCIÓN DE COLUMNAS (Corregido a V8 y con Alias para Frontend)
    return $query->select(
            'persona.apellido_pers as apellido', // Columna corregida
            'persona.nombre_pers as nombre',    // Columna corregida
            DB::raw("CASE
                        WHEN competidor.genero_competidor = 'M' THEN 'Masculino'
                        WHEN competidor.genero_competidor = 'F' THEN 'Femenino'
                        ELSE competidor.genero_competidor
                    END AS genero"),
            'persona.ci_pers as ci', // Columna corregida
            'competidor.id_departamento', // Si se usa la FK
            'institucion.nombre_inst as colegio', // Columna corregida
            'area.nombre_area as area', // Columna corregida
            'nivel.nombre_nivel as nivel', // Columna corregida
            'grado_escolaridad.nombre_grado as grado', // Columna corregida
            'olimpiada.gestion_olimp as gestion' // Columna corregida
        )
        ->orderBy('persona.apellido_pers')
        ->orderBy('persona.nombre_pers')
        ->get();
}


    public function getCompetidoresPorAreaYNivel(int $idArea, int $idNivel): Collection
    {
        $gestionActual = date('Y');

        // La unión a area_nivel ya no es directa desde competidor, sino a través de inscripcion.
        // Además, area_nivel ya no tiene id_area o id_olimpiada directo.

        $competidores = DB::table('competidor')
            ->join('persona', 'competidor.id_persona', '=', 'persona.id_persona')
            ->join('inscripcion', 'competidor.id_competidor', '=', 'inscripcion.id_competidor') // Nuevo Join
            ->join('area_nivel', 'inscripcion.id_area_nivel', '=', 'area_nivel.id_area_nivel')
            // Navegar la nueva estructura
            ->join('area_olimpiada', 'area_nivel.id_area_olimpiada', '=', 'area_olimpiada.id_area_olimpiada')
            ->join('area', 'area_olimpiada.id_area', '=', 'area.id_area')
            ->join('nivel', 'area_nivel.id_nivel', '=', 'nivel.id_nivel')
            // Resto de joins
            ->join('grado_escolaridad', 'competidor.id_grado_escolaridad', '=', 'grado_escolaridad.id_grado_escolaridad')
            ->join('institucion', 'competidor.id_institucion', '=', 'institucion.id_institucion')
            ->join('olimpiada', 'area_olimpiada.id_olimpiada', '=', 'olimpiada.id_olimpiada')

            // Filtros
            ->where('area.id_area', $idArea)
            ->where('nivel.id_nivel', $idNivel)
            ->where('olimpiada.gestion_olimp', $gestionActual) // Columna corregida

            // Selección (Corregido a V8 y con Alias)
            ->select(
                'competidor.id_competidor',
                'competidor.id_persona',
                'inscripcion.id_area_nivel', // Usamos el id_area_nivel de la inscripción
                'competidor.id_grado_escolaridad',
                'competidor.id_institucion',
                'competidor.id_departamento', // Asumo que se usa la FK
                'competidor.contacto_tutor_compe as contacto_tutor', // Columna corregida

                'persona.apellido_pers as apellido',
                'persona.nombre_pers as nombre',
                'persona.ci_pers as ci',
                'persona.telefono_pers as telefono',
                'persona.email_pers as email',

                DB::raw("CASE
                            WHEN competidor.genero_competidor = 'M' THEN 'Masculino'
                            WHEN competidor.genero_competidor = 'F' THEN 'Femenino'
                            ELSE competidor.genero_competidor
                        END AS genero"),

                'institucion.nombre_inst as colegio',
                'area.nombre_area as area',
                'nivel.nombre_nivel as nivel',
                'grado_escolaridad.nombre_grado as grado',

                'area.id_area',
                'nivel.id_nivel',
                'olimpiada.id_olimpiada' // Para cargar evaluaciones
            )
            ->orderBy('persona.apellido_pers')
            ->orderBy('persona.nombre_pers')
            ->get();

        // Obtenemos los IDs de los competidores (para el segundo paso)
        $competidorIds = $competidores->pluck('id_competidor');
        $inscripcionIds = DB::table('inscripcion')->whereIn('id_competidor', $competidorIds)->pluck('id_inscripcion');


        // Obtenemos las evaluaciones para estos competidores (buscando por id_inscripcion)
        $evaluaciones = DB::table('evaluacion')
            ->whereIn('id_inscripcion', $inscripcionIds) // CRÍTICO: Buscar por id_inscripcion
            ->select(
                'id_evaluacion',
                'nota_evalu as nota', // Columna corregida + Alias
                'observacion_evalu as observaciones', // Columna corregida + Alias
                'fecha_evalu as fecha_evaluacion', // Columna corregida + Alias
                'estado_competidor_eva as estado', // Columna corregida + Alias
                'id_inscripcion', // Usaremos este campo para mapear al competidor
                'id_competencia',
                'id_evaluador_an as id_evaluadorAN' // Columna corregida + Alias
            )
            ->get()
            // Agrupar por el ID del competidor, no por id_inscripcion (requiere join adicional en PHP)
            ->map(function($item) {
                // Agregar id_competidor al item para poder agrupar al final
                $competidorId = DB::table('inscripcion')->where('id_inscripcion', $item->id_inscripcion)->value('id_competidor');
                $item->id_competidor = $competidorId;
                return $item;
            })
            ->groupBy('id_competidor');

        // Combinamos los datos
        return $competidores->map(function ($competidor) use ($evaluaciones) {
            $competidorArray = (array) $competidor;
            // El grupo se hace por id_competidor, que ahora está en $item->id_competidor
            $competidorArray['evaluaciones'] = $evaluaciones->get($competidor->id_competidor, []);
            return (object) $competidorArray;
        });
    }
     public function getListaGrados(int $idNivel): Collection
    {
        if ($idNivel <= 0) {
            return collect();
        }

        // Se usa la tabla intermedia nivel_grado para obtener los grados permitidos en este nivel
        $gradosIds = DB::table('nivel_grado')
            ->join('area_nivel', 'nivel_grado.id_area_nivel', '=', 'area_nivel.id_area_nivel')
            ->join('area_olimpiada', 'area_nivel.id_area_olimpiada', '=', 'area_olimpiada.id_area_olimpiada')
            ->join('olimpiada', 'area_olimpiada.id_olimpiada', '=', 'olimpiada.id_olimpiada')
            ->where('area_nivel.id_nivel', $idNivel)
            ->where('area_nivel.es_activo_area_nivel', true) // Columna corregida
            ->where('olimpiada.gestion_olimp', date('Y')) // Columna corregida
            ->distinct()
            ->pluck('nivel_grado.id_grado_escolaridad')
            ->values();

        if ($gradosIds->isEmpty()) {
            return collect();
        }

        return GradoEscolaridad::whereIn('id_grado_escolaridad', $gradosIds)
            // Columna corregida: nombre_grado
            ->orderBy('nombre_grado')
            ->get();
    }

    public function getListaDepartamento()
    {
        // Columna corregida: nombre_dep
        return Departamento::select('id_departamento', 'nombre_dep')->get();
    }

    public function getListaGeneros(): array
    {
        // Retorna un array de géneros
        return [
            ['id' => 'M', 'nombre' => 'Masculino'],
            ['id' => 'F', 'nombre' => 'Femenino']
        ];
    }
}
