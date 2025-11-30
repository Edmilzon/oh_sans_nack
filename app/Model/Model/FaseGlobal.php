<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FaseGlobal extends Model
{
    use HasFactory;

    protected $table = 'fase_global';
    protected $primaryKey = 'id_fase_global';

    protected $fillable = [
        'codigo',
        'nombre',
        'orden',
    ];
}
