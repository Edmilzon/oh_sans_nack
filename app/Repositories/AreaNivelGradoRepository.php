<?php

namespace App\Repositories;

use App\Model\AreaNivelGrado;
use App\Model\AreaNivel;
use Illuminate\Database\Eloquent\Collection;

class AreaNivelGradoRepository
{
    public function getByAreaNivel(int $id_area_nivel): Collection
    {
        return AreaNivelGrado::where('id_area_nivel', $id_area_nivel)
            ->with('gradoEscolaridad')
            ->get();
    }

    public function attachGrados(int $id_area_nivel, array $grados_ids): void
    {
        $areaNivel = AreaNivel::findOrFail($id_area_nivel);
        $areaNivel->gradosEscolaridad()->syncWithoutDetaching($grados_ids);
    }

    public function detachGrados(int $id_area_nivel, array $grados_ids): void
    {
        $areaNivel = AreaNivel::findOrFail($id_area_nivel);
        $areaNivel->gradosEscolaridad()->detach($grados_ids);
    }

    public function syncGrados(int $id_area_nivel, array $grados_ids): void
    {
        $areaNivel = AreaNivel::findOrFail($id_area_nivel);
        $areaNivel->gradosEscolaridad()->sync($grados_ids);
    }

    public function exists(int $id_area_nivel, int $id_grado_escolaridad): bool
    {
        return AreaNivelGrado::where('id_area_nivel', $id_area_nivel)
            ->where('id_grado_escolaridad', $id_grado_escolaridad)
            ->exists();
    }

    public function create(array $data): AreaNivelGrado
    {
        return AreaNivelGrado::create($data);
    }

    public function deleteByAreaNivel(int $id_area_nivel): void
    {
        AreaNivelGrado::where('id_area_nivel', $id_area_nivel)->delete();
    }
}