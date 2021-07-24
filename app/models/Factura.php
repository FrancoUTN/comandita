<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Factura extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'id';
    protected $table = 'facturas';
    public $incrementing = true;
    public $timestamps = false;

    const CREATED_AT = 'fechaAlta';
    const DELETED_AT = 'fechaBaja';

    protected $fillable = [
        'codigo_mesa', 'importe', 'fechaAlta', 'fechaBaja'
    ];
}