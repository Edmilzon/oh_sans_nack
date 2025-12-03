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

    return DB::table('area_nivel as an')
        ->join('nivel as n', 'an.id_nivel', '=', 'n.id_nivel')
        ->join('area_olimpiada as ao', 'an.id_area_olimpiada', '=', 'ao.id_area_olimpiada')
        ->join('olimpiada as o', 'ao.id_olimpiada', '=', 'o.id_olimpiada')
        ->select(
            'n.id_nivel',
            'n.nombre as nombre_nivel'
        )
        ->where('ao.id_area', $idArea)
        ->where('o.gestion', $gestionActual)
        ->where('an.es_activo', true)
        ->distinct()
        ->orderBy('n.nombre')
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
    // Obtener áreas del responsable
    $areasDelResponsable = DB::table('responsable_area')
        ->join('area_olimpiada', 'responsable_area.id_area_olimpiada', '=', 'area_olimpiada.id_area_olimpiada')
        ->where('responsable_area.id_usuario', $idResponsable)
        ->pluck('area_olimpiada.id_area')
        ->unique()
        ->values();

    if ($areasDelResponsable->isEmpty()) {
        return collect();
    }

    // Si mandaron un texto que no es género, lo tomamos como departamento
    if ($genero && !in_array(strtolower($genero), ['m', 'f', 'masculino', 'femenino'])) {
        $departamento = $genero;
        $genero = null;
    }

    $query = DB::table('competidor')
        ->join('persona', 'competidor.id_persona', '=', 'persona.id_persona')
        ->join('area_nivel', 'competidor.id_area_nivel', '=', 'area_nivel.id_area_nivel')
        ->join('area_olimpiada', 'area_nivel.id_area_olimpiada', '=', 'area_olimpiada.id_area_olimpiada')
        ->join('area', 'area_olimpiada.id_area', '=', 'area.id_area')
        ->join('nivel', 'area_nivel.id_nivel', '=', 'nivel.id_nivel')
        ->join('grado_escolaridad', 'competidor.id_grado_escolaridad', '=', 'grado_escolaridad.id_grado_escolaridad')
        ->join('institucion', 'competidor.id_institucion', '=', 'institucion.id_institucion')
        ->join('departamento', 'competidor.id_departamento', '=', 'departamento.id_departamento')
        ->join('olimpiada', 'area_olimpiada.id_olimpiada', '=', 'olimpiada.id_olimpiada')
        ->whereIn('area.id_area', $areasDelResponsable);

    // Gestión actual
    $query->where('olimpiada.gestion', date('Y'));

    // Filtros
    if ($idArea) $query->where('area.id_area', $idArea);
    if ($idNivel) $query->where('nivel.id_nivel', $idNivel);
    if ($idGrado) $query->where('grado_escolaridad.id_grado_escolaridad', $idGrado);

    // Filtro por género
    if ($genero) {
        $g = strtolower($genero);
        if (in_array($g, ['m', 'masculino'])) {
            $query->where('competidor.genero', 'M');
        } elseif (in_array($g, ['f', 'femenino'])) {
            $query->where('competidor.genero', 'F');
        }
    }

    // Filtro por departamento (acepta id o nombre parcial, case-insensitive)
    if ($departamento) {
        if (is_numeric($departamento)) {
            $query->where('departamento.id_departamento', (int)$departamento);
        } else {
            // filtro por nombre parcial (insensible a mayúsculas)
            $query->whereRaw('LOWER(departamento.nombre) LIKE ?', ['%' . mb_strtolower($departamento) . '%']);
        }
    }

    return $query->select(
            'persona.apellido',
            'persona.nombre',
            DB::raw("CASE 
                        WHEN competidor.genero = 'M' THEN 'Masculino'
                        WHEN competidor.genero = 'F' THEN 'Femenino'
                        ELSE competidor.genero
                    END AS genero"),
            'persona.ci',
            'institucion.nombre as colegio',
            'departamento.nombre as departamento',
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
   public function getListaGradosPorAreaNivel(int $idArea, int $idNivel): Collection
{
    if ($idArea <= 0 || $idNivel <= 0) {
        return collect();
    }

    $gestionActual = date('Y');

    // 1) Buscar grados por area_nivel + area_olimpiada + area_nivel_grado
    $gradoIds = DB::table('area_nivel_grado')
        ->join('area_nivel', 'area_nivel_grado.id_area_nivel', '=', 'area_nivel.id_area_nivel')
        ->join('area_olimpiada', 'area_nivel.id_area_olimpiada', '=', 'area_olimpiada.id_area_olimpiada')
        ->join('olimpiada', 'area_olimpiada.id_olimpiada', '=', 'olimpiada.id_olimpiada')
        ->where('area_olimpiada.id_area', $idArea)
        ->where('area_nivel.id_nivel', $idNivel)
        ->where('area_nivel.es_activo', true)
        ->where('olimpiada.gestion', $gestionActual)
        ->pluck('area_nivel_grado.id_grado_escolaridad')
        ->unique()
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
