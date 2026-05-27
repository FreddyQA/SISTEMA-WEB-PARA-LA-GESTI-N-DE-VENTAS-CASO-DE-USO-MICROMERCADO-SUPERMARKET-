<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Categoria;
use App\Models\Proveedor;
use App\Models\DetallePedido;

class Producto extends Model
{
    use SoftDeletes;

    protected $table = 'productos';

    protected $primaryKey = 'idProducto';

    protected $fillable = [
        'nombre',
        'precio',
        'stock',
        'imagen',
        'idCategoria',
        'idProveedor',
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
        'precio'     => 'decimal:2',
    ];

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'idCategoria', 'idCategoria');
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'idProveedor', 'idProveedor');
    }

    // Un producto ya no se relaciona directamente con pedidos (esa FK
    // se eliminó al mover cantidad/producto a detalle_pedidos).
    public function detalles()
    {
        return $this->hasMany(DetallePedido::class, 'idProducto', 'idProducto');
    }

    public function detallePedidos()
    {
        return $this->detalles();
    }

    public function tieneStock(int $cantidad): bool
    {
        return $this->stock >= $cantidad;
    }
}
