<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Venta;

class Cliente extends Model
{
    use SoftDeletes;

    protected $table = 'clientes';

    protected $primaryKey = 'idCliente';

    protected $fillable = [
        'nombre',
        'email',
        'telefono',
        'direccion',
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
    ];

    public function ventas()
    {
        return $this->hasMany(Venta::class, 'idCliente', 'idCliente');
    }
}
