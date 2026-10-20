@extends('layouts.app')

@section('title', 'SUPERMARKET')
@section('page-title', 'SUPERMARKET')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-xl-10">
            <div class="welcome-hero mb-4">
                <div class="welcome-blob welcome-blob-1"></div>
                <div class="welcome-blob welcome-blob-2"></div>

                <div class="row g-4 align-items-center position-relative">
                    <div class="col-lg-7">
                        <div class="profile-summary">
                            <div class="avatar-xl">
                                <i class="fas fa-store"></i>
                            </div>
                            <div class="flex-grow-1 min-w-0">
                                <div class="welcome-hello">Bienvenido</div>
                                <h2 class="welcome-name mb-0">SUPERMARKET</h2>
                                <div class="welcome-rol">
                                    <i class="fas fa-bolt me-1"></i> Sistema de ventas y gestión
                                </div>
                            </div>
                        </div>

                        <p class="welcome-lead mt-3 mb-0">
                            Gestiona inventario, clientes, pedidos y reportes desde una sola plataforma moderna,
                            rápida y fácil de usar para tu negocio.
                        </p>
                    </div>

                    <div class="col-lg-5">
                        <div class="welcome-actions">
                            <a href="{{ route('login') }}" class="btn btn-primary w-100">
                                <i class="fas fa-right-to-bracket me-2"></i> Iniciar sesión
                            </a>
                            <a href="{{ route('login') }}" class="btn btn-success w-100">
                                <i class="fas fa-chart-line me-2"></i> Ver dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-4">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-body text-center">
                            <div class="mb-3 text-primary fs-2"><i class="fas fa-boxes-stacked"></i></div>
                            <h5 class="fw-bold">Inventario</h5>
                            <p class="text-muted mb-0">Control total de stock y productos.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-body text-center">
                            <div class="mb-3 text-success fs-2"><i class="fas fa-cart-shopping"></i></div>
                            <h5 class="fw-bold">Ventas</h5>
                            <p class="text-muted mb-0">Pedidos, clientes y reportes automatizados.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-body text-center">
                            <div class="mb-3 text-warning fs-2"><i class="fas fa-chart-column"></i></div>
                            <h5 class="fw-bold">Insights</h5>
                            <p class="text-muted mb-0">Tendencias y KPIs para decisiones rápidas.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
