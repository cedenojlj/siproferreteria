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
    Route::get('/exchange-rates', \App\Livewire\ExchangeRateManager::class)->name('exchange-rates'); 
    Route::get('/reports/sales', [ReportController::class, 'salesReport'])->name('reports.sales');
    Route::get('/reports/purchases', [ReportController::class, 'purchasesReport'])->name('reports.purchases');
    Route::get('/reports/inventory', [ReportController::class, 'inventoryReport'])->name('reports.inventory');
    Route::get('/reports/top-products', [ReportController::class, 'topProductsReport'])->name('reports.top-products');
    //Route::get('/company', \App\Livewire\Company\EditCompany::class)->name('company.edit');

    Route::get('/company', function () {
        return view('company');
    })->name('company.edit');

     Route::get('/users', function () {
        return view('userscrud');
    })->name('userscrud');

    Route::get('/categories', function () {
        return view('categories');
    })->name('categories');

       Route::get('/unit-measures', function () {
        return view('unit_measures');
    })->name('unit-measures');

     Route::get('/products', function () {
        return view('products');
    })->name('products');

      Route::get('/clientes', function () {
        return view('customers');
    })->name('clientes');

     Route::get('/proveedores', function () {
        return view('suppliers');
    })->name('proveedores');

    Route::get('/exchange-rates', function () {
        return view('exchange_rate');
    })->name('exchange_rates');





     Route::get('/sales', function () {
        return view('sales');
    })->name('sales');

     Route::get('/purchases', function () {
        return view('purchases');
    })->name('purchases');

     Route::get('/inventory-movements', function () {
        return view('inventory_movements');
    })->name('inventory-movements');

     Route::get('/refunds', function () {
        return view('refunds');
    })->name('refunds');

     Route::get('/payments', function () {
        return view('payments');
    })->name('payments');

    Route::get('/customers', \App\Livewire\CustomerCrud::class)->name('customers.index');
    Route::get('/suppliers', \App\Livewire\SupplierCrud::class)->name('suppliers.index');
    Route::get('/sales', \App\Livewire\SaleCrud::class)->name('sales.index');
    Route::get('/purchases', \App\Livewire\PurchaseCrud::class)->name('purchases.index');
    Route::get('/inventory-movements', \App\Livewire\InventoryMovementCrud::class)->name('inventory-movements.index');
    Route::get('/refunds', \App\Livewire\RefundCrud::class)->name('refunds.index');
    Route::get('/payments', \App\Livewire\PaymentCrud::class)->name('payments.index');

    Route::get('/management/roles-permissions', \App\Livewire\RolePermissionManager::class)
        ->middleware('can:manage_roles')
        ->name('roles.permissions.manager');
});