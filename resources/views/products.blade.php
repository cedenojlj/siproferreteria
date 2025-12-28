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
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="mb-0">Productos</h1>
            <a href="{{ route('reports.inventory') }}" class="btn btn-primary" target="_blank">
                <i class="fas fa-file-pdf"></i> Descargar Reporte de Inventario (PDF)
            </a>
        </div>
        <div class="row">
            <livewire:product-crud />
        </div>

        
    </div>
@endsection