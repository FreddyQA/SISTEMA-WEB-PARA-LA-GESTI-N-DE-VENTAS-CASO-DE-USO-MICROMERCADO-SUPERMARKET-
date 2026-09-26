<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * StorePedidoRequest/UpdatePedidoRequest ya exigen 'items.*.idProducto'
     * => distinct, para que un mismo producto no aparezca en dos líneas
     * del mismo pedido (si el usuario necesita más cantidad, debe sumarla
     * en una sola línea). Esa regla vivía solo en la capa de validación
     * de Laravel; esta migración la refuerza con un índice único
     * compuesto (idPedido, idProducto) en la base de datos, consistente
     * con el resto de reglas de negocio del proyecto que tienen un
     * respaldo a nivel de motor además de la validación en el formulario.
     */
    public function up(): void
    {
        Schema::table('detalle_pedidos', function (Blueprint $table) {
            $table->unique(['idPedido', 'idProducto'], 'detalle_pedidos_pedido_producto_unique');
        });
    }

    public function down(): void
    {
        Schema::table('detalle_pedidos', function (Blueprint $table) {
            $table->dropUnique('detalle_pedidos_pedido_producto_unique');
        });
    }
};
