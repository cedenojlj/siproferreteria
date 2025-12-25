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
        <h1 class="mb-4">Tasa de Cambio</h1>
        <div class="row">
            <livewire:exchange-rate-manager />
        </div>

        
    </div>
@endsection