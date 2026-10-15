<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoteInventario extends Model
{
    protected $table = 'lotes_inventario';

    protected $primaryKey = 'idLote';

    protected $fillable = [
        'idProducto',
        'cantidad',
        'fecha_vencimiento',
    ];

    protected $casts = [
        'fecha_vencimiento' => 'date',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'idProducto', 'idProducto');
    }
}
