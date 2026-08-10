<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Cliente;
use App\Models\DetalleVenta;
use App\Models\User;
use App\Services\SyncService;

class Venta extends Model
{
    use SoftDeletes;

    protected static function booted(): void
    {
        static::saved(function () {
            SyncService::touch('ventas');
            SyncService::touch('productos');
        });
        static::deleted(function () {
            SyncService::touch('ventas');
            SyncService::touch('productos');
        });
        static::restored(function () {
            SyncService::touch('ventas');
            SyncService::touch('productos');
        });
    }

    protected $table = 'ventas';

    protected $primaryKey = 'idVenta';

    // idCliente, idUsuario, total, estado y fecha viven en la cabecera.
    // Los productos y cantidades viven en detalle_ventas (ver DetalleVenta).
    protected $fillable = [
        'idCliente',
        'idUsuario',
        'total',
        'estado',
        'fecha',
        'numero',
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
        return $this->hasMany(DetalleVenta::class, 'idVenta', 'idVenta');
    }
}
