<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Encuesta extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'id';
    protected $table = 'encuestas';
    public $incrementing = true;
    // public $timestamps = false;

    const CREATED_AT = 'fechaAlta';
    const UPDATED_AT = NULL;
    const DELETED_AT = 'fechaBaja';

    protected $fillable = [
        'codigo_mesa', 'puntos_mesa', 'puntos_restaurante', 'puntos_mozo', 'puntos_cocinero', 'experiencia' ,'fechaAlta', 'fechaBaja'
    ];
}