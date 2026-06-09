<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Categoria;
use App\Models\DetalleVenta;
use App\Services\SyncService;

class Producto extends Model
{
    use SoftDeletes;

    protected static function booted(): void
    {
        static::saved(fn () => SyncService::touch('productos'));
        static::deleted(fn () => SyncService::touch('productos'));
        static::restored(fn () => SyncService::touch('productos'));
        static::forceDeleted(fn () => SyncService::touch('productos'));
    }

    protected $table = 'productos';

    protected $primaryKey = 'idProducto';

    protected $fillable = [
        'nombre',
        'precio',
        'stock',
        'imagen',
        'idCategoria',
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
        'precio'     => 'decimal:2',
    ];

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'idCategoria', 'idCategoria');
    }

    public function lotes()
    {
        return $this->hasMany(LoteInventario::class, 'idProducto', 'idProducto')
            ->orderByRaw('fecha_vencimiento IS NULL')
            ->orderBy('fecha_vencimiento')
            ->orderBy('idLote');
    }

    public function getStockDisponibleAttribute(): int
    {
        $lotes = $this->relationLoaded('lotes')
            ? $this->lotes
            : $this->lotes()->get();

        if ($lotes->isEmpty()) {
            return (int) $this->stock;
        }

        $hoy = now()->toDateString();

        return (int) $lotes
            ->filter(fn (LoteInventario $lote) => $lote->fecha_vencimiento === null || $lote->fecha_vencimiento->toDateString() >= $hoy)
            ->sum('cantidad');
    }

    // Un producto ya no se relaciona directamente con ventas (esa FK
    // se eliminó al mover cantidad/producto a detalle_ventas).
    public function detalles()
    {
        return $this->hasMany(DetalleVenta::class, 'idProducto', 'idProducto');
    }

    public function detalleVentas()
    {
        return $this->detalles();
    }

    public function tieneStock(int $cantidad): bool
    {
        return $this->stock_disponible >= $cantidad;
    }
}
