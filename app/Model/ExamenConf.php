<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamenConf extends Model
{
    use HasFactory;

    protected $table = 'examen_conf';
    protected $primaryKey = 'id_examen_conf';

    protected $fillable = [
        'id_competencia',
        'nombre',
        'ponderacion',
        'maxima_nota',
    ];

    public function competencia(): BelongsTo
    {
        return $this->belongsTo(Competencia::class, 'id_competencia', 'id_competencia');
    }

    public function evaluaciones(): HasMany
    {
        return $this->hasMany(Evaluacion::class, 'id_examen_conf', 'id_examen_conf');
    }
}
