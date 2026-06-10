<?php

namespace App\Providers;

use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\Producto;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Paginación en español (Bootstrap 5) en todo el sistema.
        Paginator::defaultView('vendor.pagination.custom');
        Paginator::defaultSimpleView('vendor.pagination.custom');

        // Total de elementos en la papelera, para el contador del sidebar. Solo se
        // consulta para administradores (el invitado no ve la papelera).
        View::composer('layouts.app', function ($view) {
            $usuario = auth()->user();

            $view->with('papeleraTotal', ($usuario && $usuario->esAdmin())
                ? Producto::onlyTrashed()->count()
                    + Categoria::onlyTrashed()->count()
                    + Cliente::onlyTrashed()->count()
                : 0);
        });
    }
}
