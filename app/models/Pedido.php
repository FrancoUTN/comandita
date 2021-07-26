<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pedido extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'id';
    protected $table = 'pedidos';
    public $incrementing = true;
    public $timestamps = false;
    
    const DELETED_AT = 'fechaBaja';

    protected $fillable = [
        'codigo', 'id_producto', 'codigo_mesa', 'cantidad', 'estado',
        'hora_inicio', 'hora_estimada', 'hora_entrega', 'fechaBaja'
    ];
}