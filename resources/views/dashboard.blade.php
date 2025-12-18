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
            <div class="col-md-6 col-lg-3 mb-4">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h5 class="card-title">Card Título 1</h5>
                        <p class="card-text">Contenido de la tarjeta 1.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3 mb-4">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h5 class="card-title">Card Título 2</h5>
                        <p class="card-text">Contenido de la tarjeta 2.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3 mb-4">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <h5 class="card-title">Card Título 3</h5>
                        <p class="card-text">Contenido de la tarjeta 3.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3 mb-4">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h5 class="card-title">Card Título 4</h5>
                        <p class="card-text">Contenido de la tarjeta 4.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12 mb-4">
                <div class="card">
                    <div class="card-header">
                        Gráfico de Ventas
                    </div>
                    <div class="card-body">
                        <p>Contenido del gráfico aquí (ej. un placeholder para un gráfico de barras).</p>
                        <div style="height: 200px; background-color: #f8f9fa; border: 1px solid #dee2e6; display: flex; align-items: center; justify-content: center;">
                            Placeholder para Gráfico
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection