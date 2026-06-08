<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Producto;
use App\Services\SyncService;

class Categoria extends Model
{
    use SoftDeletes;

    protected static function booted(): void
    {
        static::saved(function () {
            SyncService::touch('categorias');
            SyncService::touch('productos');
        });
        static::deleted(function () {
            SyncService::touch('categorias');
            SyncService::touch('productos');
        });
        static::restored(function () {
            SyncService::touch('categorias');
            SyncService::touch('productos');
        });
        static::forceDeleted(function () {
            SyncService::touch('categorias');
            SyncService::touch('productos');
        });
    }

    protected $table = 'categorias';

    protected $primaryKey = 'idCategoria';

    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
    ];

    public function productos()
    {
        return $this->hasMany(Producto::class, 'idCategoria', 'idCategoria');
    }
}
