<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Producto;

class Categoria extends Model
{
    use SoftDeletes;

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
