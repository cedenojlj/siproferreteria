@extends('layouts.app')

@section('title', 'Devoluciones')

@section('header')
    @include('partials.header')
@endsection

@section('sidebar')
    @include('partials.sidebar')
@endsection

@section('content')
    <div class="container-fluid mt-4">
        <livewire:refund-crud />
    </div>
@endsection
