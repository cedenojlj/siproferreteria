@extends('layouts.app')

@section('title', 'Dashboard')

@section('header')
    @include('partials.header')
@endsection

@section('sidebar')
    @include('partials.sidebar')
@endsection

@section('content')
    <div class="container-fluid mt-4">
        <h1 class="mb-4">Dashboard</h1>
        <div class="row">
            <!-- Ventas del Día -->
            <div class="col-md-6 col-lg-3 mb-4">
                <div class="card bg-primary text-white shadow">
                    <div class="card-body">
                        <h5 class="card-title">Ventas del Día</h5>
                        <h2 class="display-4">$ {{ number_format($kpis['ventasHoy'] ?? 0, 2) }}</h2>
                        <p class="card-text">{{ $kpis['numVentasHoy'] ?? 0 }} transacciones</p>
                    </div>
                </div>
            </div>
            <!-- Ventas del Mes -->
            <div class="col-md-6 col-lg-3 mb-4">
                <div class="card bg-info text-white shadow">
                    <div class="card-body">
                        <h5 class="card-title">Ventas del Mes</h5>
                        <h2 class="display-4">$ {{ number_format($kpis['ventasMes'] ?? 0, 2) }}</h2>
                        <p class="card-text">Acumulado mensual</p>
                    </div>
                </div>
            </div>
            <!-- Total Clientes -->
            <div class="col-md-6 col-lg-3 mb-4">
                <div class="card bg-success text-white shadow">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title">Total de Clientes</h5>
                            <h2 class="display-4">{{ $kpis['numClientes'] ?? 0 }}</h2>
                            <p class="card-text mb-0">Clientes registrados</p>
                        </div>
                        <i class="fas fa-users fa-3x" style="opacity: 0.5;"></i>
                    </div>
                </div>
            </div>
            <!-- Total Productos -->
            <div class="col-md-6 col-lg-3 mb-4">
                <div class="card bg-danger text-white shadow">
                     <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title">Total de Productos</h5>
                            <h2 class="display-4">{{ $kpis['numProductos'] ?? 0 }}</h2>
                            <p class="card-text mb-0">Productos en inventario</p>
                        </div>
                        <i class="fas fa-box-open fa-3x" style="opacity: 0.5;"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12 mb-4">
                <div class="card shadow">
                    <div class="card-header">
                        <i class="fas fa-chart-bar me-1"></i>
                        Ventas de los Últimos 7 Días
                    </div>
                    <div class="card-body">
                        <canvas id="salesChart" width="100%" height="30"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Script para Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('salesChart').getContext('2d');
            
            const salesChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: @json($kpis['chart']['labels'] ?? []),
                    datasets: [{
                        label: 'Ventas ($)',
                        data: @json($kpis['chart']['data'] ?? []),
                        backgroundColor: 'rgba(0, 123, 255, 0.5)',
                        borderColor: 'rgba(0, 123, 255, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '$' + new Intl.NumberFormat('en-US').format(value);
                                }
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed.y !== null) {
                                        label += new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(context.parsed.y);
                                    }
                                    return label;
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
@endsection