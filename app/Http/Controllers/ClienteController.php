<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cliente;

class ClienteController extends Controller
{
    // LISTAR
    public function index()
    {
        $clientes = Cliente::all();
        return view('clientes.index', compact('clientes'));
    }

    // FORM CREAR
    public function create()
    {
        return view('clientes.create');
    }

    // GUARDAR
    public function store(Request $request)
    {
        $datos = $request->validate([
            'nombre'    => 'required|string|min:2|max:100|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s\.\,\-\']+$/',
            'email'     => 'required|email|max:100',
            'telefono'  => 'nullable|string|min:7|max:20|regex:/^[\+]?[0-9\s\-\(\)]+$/',
            'direccion' => 'nullable|string|min:5|max:150|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s\.\,\-\#\/]+$/',
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.min'      => 'El nombre debe tener al menos 2 caracteres.',
            'nombre.max'      => 'El nombre no debe superar los 100 caracteres.',
            'nombre.regex'    => 'El nombre solo puede contener letras, espacios y . , - \'',
            'email.required'  => 'El correo es obligatorio.',
            'email.email'     => 'Ingrese un correo válido.',
            'email.max'       => 'El correo no debe superar los 100 caracteres.',
            'telefono.min'    => 'El teléfono debe tener al menos 7 caracteres.',
            'telefono.max'    => 'El teléfono no debe superar los 20 caracteres.',
            'telefono.regex'  => 'El teléfono solo puede contener números, espacios, guiones y paréntesis.',
            'direccion.min'   => 'La dirección debe tener al menos 5 caracteres.',
            'direccion.max'   => 'La dirección no debe superar los 150 caracteres.',
            'direccion.regex' => 'La dirección solo puede contener letras, números, espacios y . , - # /',
        ]);

        // Verificar si el correo ya existe (activo o en papelera)
        $existente = Cliente::withTrashed()
            ->where('email', $datos['email'])
            ->first();

        if ($existente) {
            if ($existente->trashed()) {
                // Si estaba en la papelera, restaurar y actualizar datos
                $existente->restore();
                $existente->update($datos);

                return redirect()->route('clientes.index')
                    ->with('success', 'El cliente "' . $existente->nombre . '" se encontraba en la papelera y ha sido restaurado con éxito.');
            }

            return back()
                ->withErrors(['email' => 'Este correo ya está registrado por un cliente activo.'])
                ->withInput();
        }

        Cliente::create($datos);

        return redirect()->route('clientes.index')
            ->with('success', 'Cliente registrado correctamente');
    }

    // FORM EDITAR
    public function edit($id)
    {
        $cliente = Cliente::findOrFail($id);
        return view('clientes.edit', compact('cliente'));
    }

    // ACTUALIZAR
    public function update(Request $request, $id)
    {
        $cliente = Cliente::findOrFail($id);

        $datos = $request->validate([
            'nombre'    => 'required|string|min:2|max:100|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s\.\,\-\']+$/',
            'email'     => 'required|email|max:100',
            'telefono'  => 'nullable|string|min:7|max:20|regex:/^[\+]?[0-9\s\-\(\)]+$/',
            'direccion' => 'nullable|string|min:5|max:150|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s\.\,\-\#\/]+$/',
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.min'      => 'El nombre debe tener al menos 2 caracteres.',
            'nombre.max'      => 'El nombre no debe superar los 100 caracteres.',
            'nombre.regex'    => 'El nombre solo puede contener letras, espacios y . , - \'',
            'email.required'  => 'El correo es obligatorio.',
            'email.email'     => 'Ingrese un correo válido.',
            'email.max'       => 'El correo no debe superar los 100 caracteres.',
            'telefono.min'    => 'El teléfono debe tener al menos 7 caracteres.',
            'telefono.max'    => 'El teléfono no debe superar los 20 caracteres.',
            'telefono.regex'  => 'El teléfono solo puede contener números, espacios, guiones y paréntesis.',
            'direccion.min'   => 'La dirección debe tener al menos 5 caracteres.',
            'direccion.max'   => 'La dirección no debe superar los 150 caracteres.',
            'direccion.regex' => 'La dirección solo puede contener letras, números, espacios y . , - # /',
        ]);

        // Verificar si otro cliente ya usa este correo (activo o en papelera)
        $existente = Cliente::withTrashed()
            ->where('email', $datos['email'])
            ->where('idCliente', '!=', $cliente->idCliente)
            ->first();

        if ($existente) {
            $estado = $existente->trashed() ? 'en la papelera' : 'activo';
            return back()
                ->withErrors(['email' => "Ya existe otro cliente ($estado) con este correo electrónico."])
                ->withInput();
        }

        $cliente->update($datos);

        return redirect()->route('clientes.index')
            ->with('success', 'Cliente actualizado correctamente');
    }

    // ELIMINAR
    public function destroy($id)
    {
        $cliente = Cliente::findOrFail($id);

        $cantPedidos = $cliente->pedidos()->count();
        if ($cantPedidos > 0) {
            return redirect()->route('clientes.index')
                ->with('error', "No se puede eliminar el cliente \"{$cliente->nombre}\" porque tiene {$cantPedidos} pedido(s) asociado(s).");
        }

        $cliente->delete();

        return redirect()->route('clientes.index')
            ->with('success', 'Cliente eliminado correctamente');
    }
}