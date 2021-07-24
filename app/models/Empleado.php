<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Empleado extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'id';
    protected $table = 'empleados';
    public $incrementing = true;
    public $timestamps = false;

    const CREATED_AT = 'fechaAlta';
    const DELETED_AT = 'fechaBaja';

    protected $fillable = [
        'nombre', 'clave', 'id_sector', 'operaciones', 'fechaAlta', 'fechaBaja'
    ];
}