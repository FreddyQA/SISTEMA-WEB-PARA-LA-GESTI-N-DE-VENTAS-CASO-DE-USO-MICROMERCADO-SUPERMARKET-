<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Pedido;

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

    public function pedidos()
    {
        return $this->hasMany(Pedido::class, 'idCliente', 'idCliente');
    }
}
