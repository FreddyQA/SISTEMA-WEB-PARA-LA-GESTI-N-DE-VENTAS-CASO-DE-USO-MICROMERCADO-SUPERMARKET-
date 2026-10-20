<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Cliente;
use App\Models\DetallePedido;
use App\Models\User;

class Pedido extends Model
{
    use SoftDeletes;

    protected $table = 'pedidos';

    protected $primaryKey = 'idPedido';

    // idCliente, idUsuario, total, estado y fecha viven en la cabecera.
    // Los productos y cantidades viven en detalle_pedidos (ver DetallePedido).
    protected $fillable = [
        'idCliente',
        'idUsuario',
        'total',
        'estado',
        'fecha',
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
        'total'      => 'decimal:2',
        'fecha'      => 'date',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'idCliente', 'idCliente');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'idUsuario', 'id');
    }

    public function detalles()
    {
        return $this->hasMany(DetallePedido::class, 'idPedido', 'idPedido');
    }
}
