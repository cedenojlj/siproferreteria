@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Módulo de Reportes</h1>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Generar Reportes</h6>
        </div>
        <div class="card-body">
            {{-- Reporte de Inventario --}}
            <div class="report-section border-bottom mb-3 pb-3">
                <h5 class="mb-3">Reporte de Inventario</h5>
                <p>Genera un listado completo de todos los productos y su stock actual.</p>
                <a href="{{ route('reports.inventory') }}" class="btn btn-info" target="_blank">
                    <i class="bi bi-file-earmark-text"></i> Generar Reporte de Inventario
                </a>
            </div>

            {{-- Reporte de Productos Más Vendidos --}}
            <div class="report-section">
                <h5 class="mb-3">Reporte de Productos Más Vendidos</h5>
                <p>Analiza los productos más vendidos en un período de tiempo específico. Si no se seleccionan fechas, se considerará todo el historial.</p>
                <form action="{{ route('reports.top_selling_products') }}" method="GET" target="_blank">
                    <div class="row align-items-end">
                        <div class="col-md-4">
                            <label for="start_date_top_selling">Fecha de Inicio:</label>
                            <input type="date" id="start_date_top_selling" name="start_date" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label for="end_date_top_selling">Fecha de Fin:</label>
                            <input type="date" id="end_date_top_selling" name="end_date" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-graph-up"></i> Generar Reporte de Más Vendidos
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            
            {{-- Reporte de Cierre de Caja (Corte Z) --}}
            <div class="report-section border-top mt-3 pt-3">
                <h5 class="mb-3">Cierre de Caja Diario (Reporte Z)</h5>
                <p>Genere el reporte de cierre para consolidar todas las transacciones desde el último corte hasta el momento actual. Esta acción registrará el cierre y permitirá la descarga de un comprobante.</p>
                @livewire('reportes.cierre-caja')
            </div>

        </div>
    </div>
</div>
@endsection
