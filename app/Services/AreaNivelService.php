<?php

namespace App\Services;

use App\Model\AreaNivel;
use App\Model\Olimpiada;
use App\Model\AreaOlimpiada;
use App\Model\Area;
use App\Model\Nivel;
use App\Repositories\AreaNivelRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class AreaNivelService
{
    protected $areaNivelRepository;

    public function __construct(AreaNivelRepository $areaNivelRepository)
    {
        $this->areaNivelRepository = $areaNivelRepository;
    }

    private function obtenerOlimpiadaActual(): Olimpiada
    {
        $gestionActual = date('Y');
        $nombreOlimpiada = "Olimpiada Científica Estudiantil $gestionActual";

        return Olimpiada::firstOrCreate(
            ['gestion' => "$gestionActual"],
            ['nombre' => $nombreOlimpiada]
        );
    }

    public function getAreaNivelList(): Collection
    {
        return $this->areaNivelRepository->getAllAreasNiveles();
    }

    public function getAreaNivelByArea(int $id_area): Collection
    {
        $olimpiadaActual = $this->obtenerOlimpiadaActual();
        return $this->areaNivelRepository->getByArea($id_area, $olimpiadaActual->id_olimpiada);
    }

    public function getAreaNivelById(int $id): ?array
    {
        $areaNivel = $this->areaNivelRepository->getById($id);

        if (!$areaNivel) {
            return null;
        }

        return [
            'area_nivel' => $areaNivel,
            'message' => 'Relación área-nivel encontrada'
        ];
    }

    public function createAreaNivel(array $data): array
    {
        try {
            $olimpiadaActual = $this->obtenerOlimpiadaActual();

            // Verificar si existe el área-olimpiada
            $areaOlimpiada = AreaOlimpiada::where('id_area', $data['id_area'])
                ->where('id_olimpiada', $olimpiadaActual->id_olimpiada)
                ->first();

            if (!$areaOlimpiada) {
                throw new \Exception("El área no está asociada a la olimpiada actual");
            }

            // Verificar si ya existe
            $existing = $this->areaNivelRepository->getByAreaAndNivel(
                $areaOlimpiada->id_area_olimpiada,
                $data['id_nivel']
            );

            if ($existing) {
                throw new \Exception("Ya existe esta combinación de área y nivel para la gestión actual");
            }

            $areaNivel = $this->areaNivelRepository->create([
                'id_area_olimpiada' => $areaOlimpiada->id_area_olimpiada,
                'id_nivel' => $data['id_nivel'],
                'es_activo' => $data['es_activo'] ?? true
            ]);

            return [
                'area_nivel' => $areaNivel,
                'olimpiada' => $olimpiadaActual->gestion,
                'message' => 'Relación área-nivel creada exitosamente'
            ];

        } catch (\Exception $e) {
            Log::error('[SERVICE] Error en createAreaNivel:', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function updateAreaNivel(int $id, array $data): array
    {
        $areaNivel = AreaNivel::find($id);

        if (!$areaNivel) {
            throw new \Exception('Relación área-nivel no encontrada');
        }

        $areaNivel->update($data);

        return [
            'area_nivel' => $areaNivel,
            'message' => 'Relación área-nivel actualizada exitosamente'
        ];
    }

    public function deleteAreaNivel(int $id): array
    {
        $areaNivel = AreaNivel::find($id);

        if (!$areaNivel) {
            throw new \Exception('Relación área-nivel no encontrada');
        }

        $areaNivel->delete();

        return [
            'message' => 'Relación área-nivel eliminada exitosamente'
        ];
    }

    public function getAreaNivelActuales(): array
    {
        // 1. Obtener la última olimpiada (o la activa)
        $olimpiadaActual = Olimpiada::latest('id_olimpiada')->first();

        if (!$olimpiadaActual) {
            return [];
        }

        // 2. Traer las Áreas asociadas a esta olimpiada con sus Niveles
        // Usamos 'AreaOlimpiada' como pivote principal para filtrar por gestión
        $areaOlimpiadas = AreaOlimpiada::with(['area', 'areaNiveles.nivel'])
            ->where('id_olimpiada', $olimpiadaActual->id_olimpiada)
            ->get();

        // 3. Mapeo exacto al JSON solicitado
        return $areaOlimpiadas->map(function (AreaOlimpiada $ao) {

            // Mapear los niveles dentro de esta área
            $niveles = $ao->areaNiveles->map(function ($an) {
                return [
                    'id_area_nivel' => (string) $an->id_area_nivel,
                    'id_nivel'      => (string) $an->id_nivel, // CORREGIDO: id_nivel
                    // Aquí tomamos el nombre del Nivel.
                    // Nota: Si en tu DB el nombre es "Nivel 1" pero quieres "1ro de Sec",
                    // asegúrate que el modelo Nivel tenga ese texto o usa la relación con Grado.
                    'nombre'        => $an->nivel->nombre
                ];
            })->values(); // Resetear índices para que sea array [] y no objeto {}

            return [
                'id_area' => (string) $ao->area->id_area, // CORREGIDO: id_area
                'area'    => $ao->area->nombre,
                'niveles' => $niveles
            ];

        })->values()->toArray();
    }

    public function getByAreaOlimpiada(int $id_area_olimpiada): Collection
    {
        return $this->areaNivelRepository->getByAreaOlimpiada($id_area_olimpiada);
    }

    public function getAreasConNivelesPorOlimpiada(int $idOlimpiada): array
{
    $olimpiada = Olimpiada::findOrFail($idOlimpiada);
    $areas = Area::with([
        'areaOlimpiada' => function($query) use ($idOlimpiada) {
            $query->where('id_olimpiada', $idOlimpiada);
        },
        'areaOlimpiada.areaNiveles.nivel:id_nivel,nombre'
    ])
    ->whereHas('areaOlimpiada.areaNiveles')
    ->get(['id_area', 'nombre']);

    $resultado = $areas->map(function($area) {
        $niveles = collect();
        foreach ($area->areaOlimpiada as $areaOlimpiada) {
            foreach ($areaOlimpiada->areaNiveles as $areaNivel) {
                $niveles->push([
                    'id_nivel' => $areaNivel->nivel->id_nivel,
                    'nombre' => $areaNivel->nivel->nombre
                ]);
            }
        }

        return [
            'id_area' => $area->id_area,
            'nombre' => $area->nombre,
            'niveles' => $niveles->unique('id_nivel')->values()
        ];
    });

    return [
        'areas' => $resultado->values(),
        'olimpiada' => $olimpiada->gestion,
        'message' => "Áreas con niveles obtenidas para la gestión {$olimpiada->gestion}"
    ];
}

public function getAreasConNivelesPorGestion(string $gestion): array
{
    $olimpiada = Olimpiada::where('gestion', $gestion)->firstOrFail();
    return $this->getAreasConNivelesPorOlimpiada($olimpiada->id_olimpiada);
}

public function getAllAreaNivelWithDetails(): array
{
    $areaNiveles = AreaNivel::with([
        'areaOlimpiada.area:id_area,nombre',
        'nivel:id_nivel,nombre',
        'areaOlimpiada.olimpiada:id_olimpiada,gestion'
    ])->get();

    return [
        'area_niveles' => $areaNiveles,
        'message' => 'Todas las relaciones área-nivel obtenidas con detalles'
    ];
    }

public function updateAreaNivelByArea(int $id_area, array $niveles): array
    {
    $olimpiadaActual = $this->obtenerOlimpiadaActual();
    $areaOlimpiada = AreaOlimpiada::where('id_area', $id_area)
        ->where('id_olimpiada', $olimpiadaActual->id_olimpiada)
        ->first();

    if (!$areaOlimpiada) {
        throw new \Exception("El área no está asociada a la olimpiada actual");
    }

    $updatedNiveles = [];
    foreach ($niveles as $nivelData) {
        $areaNivel = AreaNivel::where('id_area_olimpiada', $areaOlimpiada->id_area_olimpiada)
            ->where('id_nivel', $nivelData['id_nivel'])
            ->first();

        if ($areaNivel) {
            $areaNivel->update(['es_activo' => $nivelData['activo']]);
            $updatedNiveles[] = $areaNivel;
        } else {
            $newAreaNivel = AreaNivel::create([
                'id_area_olimpiada' => $areaOlimpiada->id_area_olimpiada,
                'id_nivel' => $nivelData['id_nivel'],
                'es_activo' => $nivelData['activo']
            ]);
            $updatedNiveles[] = $newAreaNivel;
        }
    }

    return [
        'area_niveles' => $updatedNiveles,
        'olimpiada' => $olimpiadaActual->gestion,
        'message' => 'Relaciones área-nivel actualizadas exitosamente para la gestión actual'
    ];
    }

public function getByGestionAndAreas(string $gestion, array $idAreas): array
    {
    $olimpiada = Olimpiada::where('gestion', $gestion)->firstOrFail();

    $areaNiveles = AreaNivel::whereHas('areaOlimpiada', function($query) use ($idAreas, $olimpiada) {
            $query->whereIn('id_area', $idAreas)
                  ->where('id_olimpiada', $olimpiada->id_olimpiada);
        })
        ->with(['areaOlimpiada.area', 'nivel'])
        ->get();

    return [
        'area_niveles' => $areaNiveles,
        'olimpiada' => $olimpiada->gestion,
        'message' => "Relaciones área-nivel obtenidas para la gestión {$gestion}"
    ];
    }
}
