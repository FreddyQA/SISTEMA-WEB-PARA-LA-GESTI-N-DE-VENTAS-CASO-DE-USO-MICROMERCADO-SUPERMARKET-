<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Proveedor extends Model
{
    use SoftDeletes;

    protected $table = 'proveedores';

    protected $primaryKey = 'idProveedor';

    protected $fillable = [
        'nombre',
        'nit',
        'telefono',
        'email',
        'direccion',
        'activo',
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
        'activo'     => 'boolean',
    ];

    // RELACIÓN: Proveedor tiene muchos Productos
    public function productos()
    {
        return $this->hasMany(Producto::class, 'idProveedor', 'idProveedor');
    }
}
