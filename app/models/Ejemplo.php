<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ejemplo extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'id';
    protected $table = ''; // tabla
    public $incrementing = true;
    public $timestamps = false;

    const DELETED_AT = 'fechaBaja';

    protected $fillable = [
        
    ]; // columnas
}