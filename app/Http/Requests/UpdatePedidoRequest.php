<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePedidoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'idCliente'            => 'required|exists:clientes,idCliente',
            'fecha'                => 'required|date|before_or_equal:today',
            'estado'               => 'required|in:completado,anulado',
            'items'                => 'required|array|min:1|max:100',
            'items.*.idProducto'   => 'required|exists:productos,idProducto|distinct',
            'items.*.cantidad'     => 'required|integer|min:1|max:9999',
        ];
    }

    public function messages(): array
    {
        return [
            'idCliente.required'          => 'Seleccione un cliente.',
            'idCliente.exists'            => 'El cliente seleccionado no existe o no es válido.',
            'fecha.required'              => 'Seleccione una fecha.',
            'fecha.date'                  => 'La fecha no es válida.',
            'fecha.before_or_equal'       => 'La fecha no puede ser futura.',
            'estado.required'             => 'Seleccione un estado.',
            'estado.in'                   => 'El estado seleccionado no es válido.',
            'items.required'              => 'Agregue al menos un producto al pedido.',
            'items.array'                 => 'El listado de productos tiene un formato no válido.',
            'items.min'                   => 'Agregue al menos un producto al pedido.',
            'items.max'                   => 'No puede agregar más de 100 productos en un pedido.',
            'items.*.idProducto.required' => 'Seleccione un producto en cada línea.',
            'items.*.idProducto.exists'   => 'Uno de los productos seleccionados no existe o fue eliminado.',
            'items.*.idProducto.distinct' => 'No repita el mismo producto en dos líneas; sume la cantidad en una sola.',
            'items.*.cantidad.required'   => 'Ingrese una cantidad para cada producto.',
            'items.*.cantidad.integer'    => 'La cantidad debe ser un número entero.',
            'items.*.cantidad.min'        => 'La cantidad debe ser al menos 1.',
            'items.*.cantidad.max'        => 'La cantidad no debe superar 9,999.',
        ];
    }
}
