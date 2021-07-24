<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Socio extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'id';
    protected $table = 'socios';
    public $incrementing = true;
    // public $timestamps = false;

    const CREATED_AT = 'fechaAlta';
    const UPDATED_AT = NULL;
    const DELETED_AT = 'fechaBaja';

    protected $fillable = [
        'nombre', 'clave', 'fechaAlta', 'fechaBaja'
    ];
}