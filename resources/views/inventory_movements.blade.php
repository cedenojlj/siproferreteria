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
        <h1 class="mb-4">Movimientos de Inventario</h1>
        <div class="row">
            <livewire:inventory-movement-crud />
        </div>

        
    </div>
@endsection