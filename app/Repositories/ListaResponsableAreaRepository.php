<?php

namespace App\Repositories;

use App\Model\GradoEscolaridad;
use App\Model\Departamento;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ListaResponsableAreaRepository
{
    public function getNivelesByArea(int $idArea): Collection
    {
        $gestionActual = date('Y');

        return DB::table('area_nivel')
            ->join('nivel', 'area_nivel.id_nivel', '=', 'nivel.id_nivel')
            ->join('olimpiada', 'area_nivel.id_olimpiada', '=', 'olimpiada.id_olimpiada')
            ->select(
                'nivel.id_nivel',
                'nivel.nombre as nombre_nivel',
            )
            ->where('area_nivel.id_area', $idArea)
            ->where('olimpiada.gestion', $gestionActual)
            ->where('area_nivel.activo', true)
            ->orderBy('nivel.nombre')
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
            ->select('area.id_area', 'area.nombre')
            ->where('responsable_area.id_usuario', $idResponsable)
            ->where('olimpiada.gestion', $gestionActual)
            ->distinct()
            ->orderBy('area.nombre')
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
    $areasDelResponsable = DB::table('responsable_area')
        ->join('area_olimpiada', 'responsable_area.id_area_olimpiada', '=', 'area_olimpiada.id_area_olimpiada')
        ->where('responsable_area.id_usuario', $idResponsable)
        ->pluck('area_olimpiada.id_area')
        ->unique()
        ->values();

    if ($areasDelResponsable->isEmpty()) {
        return collect();
    }

    if ($genero && !in_array(strtolower($genero), ['m', 'f', 'masculino', 'femenino'])) {
        $departamento = $genero;
        $genero = null;
    }

    $query = DB::table('competidor')
        ->join('persona', 'competidor.id_persona', '=', 'persona.id_persona')
        ->join('area_nivel', 'competidor.id_area_nivel', '=', 'area_nivel.id_area_nivel')
        ->join('area', 'area_nivel.id_area', '=', 'area.id_area')
        ->join('nivel', 'area_nivel.id_nivel', '=', 'nivel.id_nivel')
        ->join('grado_escolaridad', 'competidor.id_grado_escolaridad', '=', 'grado_escolaridad.id_grado_escolaridad')
        ->join('institucion', 'competidor.id_institucion', '=', 'institucion.id_institucion')
        ->whereIn('area.id_area', $areasDelResponsable);

    // 🧩 Filtro por olimpiada del año actual
    $anioActual = date('Y');
    $query->join('olimpiada', 'area_nivel.id_olimpiada', '=', 'olimpiada.id_olimpiada')
          ->where('olimpiada.gestion', $anioActual);

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
        $genero = strtolower($genero);
        if (in_array($genero, ['m', 'masculino'])) {
            $query->where('persona.genero', 'M');
        } elseif (in_array($genero, ['f', 'femenino'])) {
            $query->where('persona.genero', 'F');
        }
    }

    if ($departamento) {
        $query->whereRaw('LOWER(competidor.departamento) = ?', [strtolower($departamento)]);
    }

    return $query->select(
            'persona.apellido',
            'persona.nombre',
            DB::raw("CASE 
                        WHEN persona.genero = 'M' THEN 'Masculino'
                        WHEN persona.genero = 'F' THEN 'Femenino'
                        ELSE persona.genero
                    END AS genero"),
            'persona.ci',
            'competidor.departamento',
            'institucion.nombre as colegio',
            'area.nombre as area',
            'nivel.nombre as nivel',
            'grado_escolaridad.nombre as grado',
            'olimpiada.gestion as gestion'
        )
        ->orderBy('persona.apellido')
        ->orderBy('persona.nombre')
        ->get();
}


    public function getCompetidoresPorAreaYNivel(int $idArea, int $idNivel): Collection
    {
        $gestionActual = date('Y');
        // Primero obtenemos los competidores
        $competidores = DB::table('competidor')
            ->join('persona', 'competidor.id_persona', '=', 'persona.id_persona')
            ->join('area_nivel', 'competidor.id_area_nivel', '=', 'area_nivel.id_area_nivel')
            ->join('area', 'area_nivel.id_area', '=', 'area.id_area')
            ->join('nivel', 'area_nivel.id_nivel', '=', 'nivel.id_nivel')
            ->join('grado_escolaridad', 'competidor.id_grado_escolaridad', '=', 'grado_escolaridad.id_grado_escolaridad')
            ->join('institucion', 'competidor.id_institucion', '=', 'institucion.id_institucion')
            ->join('olimpiada', 'area_nivel.id_olimpiada', '=', 'olimpiada.id_olimpiada')
            ->where('area.id_area', $idArea)
            ->where('nivel.id_nivel', $idNivel)
            ->where('olimpiada.gestion', $gestionActual)
            ->select(
                'competidor.id_competidor',
                'competidor.id_persona',
                'competidor.id_area_nivel',
                'competidor.id_grado_escolaridad',
                'competidor.id_institucion',
                'competidor.departamento',
                'competidor.contacto_tutor',
                'persona.apellido',
                'persona.nombre',
                DB::raw("CASE 
                            WHEN persona.genero = 'M' THEN 'Masculino'
                            WHEN persona.genero = 'F' THEN 'Femenino'
                            ELSE persona.genero
                        END AS genero"),
                'persona.ci',
                'persona.telefono',
                'persona.email',
                'institucion.nombre as colegio',
                'area.nombre as area',
                'nivel.nombre as nivel',
                'grado_escolaridad.nombre as grado',
                'area_nivel.id_area',
                'area_nivel.id_nivel',
                'area_nivel.id_olimpiada'
            )
            ->orderBy('persona.apellido')
            ->orderBy('persona.nombre')
            ->get();

        // Obtenemos los IDs de los competidores
        $competidorIds = $competidores->pluck('id_competidor');

        // Obtenemos las evaluaciones para estos competidores
        $evaluaciones = DB::table('evaluacion')
            ->whereIn('id_competidor', $competidorIds)
            ->select(
                'id_evaluacion',
                'nota',
                'observaciones',
                'fecha_evaluacion',
                'estado',
                'id_competidor',
                'id_competencia',
                'id_evaluadorAN',
                'id_parametro'
            )
            ->get()
            ->groupBy('id_competidor');

        // Combinamos los datos
        return $competidores->map(function ($competidor) use ($evaluaciones) {
            $competidorArray = (array) $competidor;
            $competidorArray['evaluaciones'] = $evaluaciones->get($competidor->id_competidor, []);
            return (object) $competidorArray;
        });
    }
     public function getListaGrados(int $idNivel): Collection
    {
        if ($idNivel <= 0) {
            return collect();
        }

        $gestionActual = (int) date('Y');

        // Tomamos los id_grado_escolaridad desde area_nivel (uniendo con olimpiada vía id_olimpiada)
        $gradoIds = DB::table('area_nivel')
            ->join('olimpiada', 'area_nivel.id_olimpiada', '=', 'olimpiada.id_olimpiada')
            ->where('area_nivel.id_nivel', $idNivel)
            ->where('area_nivel.activo', true)
            ->whereNotNull('area_nivel.id_grado_escolaridad')
            ->where('olimpiada.gestion', $gestionActual)
            ->distinct()
            ->pluck('area_nivel.id_grado_escolaridad')
            ->filter()
            ->values();

        if ($gradoIds->isEmpty()) {
            return collect();
        }

        return GradoEscolaridad::whereIn('id_grado_escolaridad', $gradoIds)
            ->orderBy('nombre')
            ->get();
    }
     public function getListaDepartamento()
{
    return Departamento::all();
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
