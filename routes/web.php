<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReportController;
use App\Livewire\ProductManager;


Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

Route::get('/logout', function () {
    Auth::logout();
    return redirect('/');
})->name('logout');


Route::middleware(['auth', 'web'])->group(function () {
    Route::get('/pos', \App\Livewire\PointOfSale::class)->name('pos'); 
    Route::get('/productsmanager', \App\Livewire\ProductManager::class)->name('productsmanager'); 
    Route::get('/reports/sales', [ReportController::class, 'salesReport'])->name('reports.sales');
    Route::get('/reports/purchases', [ReportController::class, 'purchasesReport'])->name('reports.purchases');
    Route::get('/reports/inventory', [ReportController::class, 'inventoryReport'])->name('reports.inventory');
    Route::get('/reports/top-products', [ReportController::class, 'topProductsReport'])->name('reports.top-products');
    //Route::get('/company', \App\Livewire\Company\EditCompany::class)->name('company.edit');

    Route::get('/company', function () {
        return view('company');
    })->name('company.edit');

    Route::get('/exchange-rates', \App\Livewire\ExchangeRateManager::class)->name('exchange-rates');
    
});