<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TicketController;
use App\Livewire\ProductManager;
use App\Livewire\CashierSales;
use App\Models\Purchase;

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
    //Route::get('/cashier', CashierSales::class)->name('cashier.sales');
    Route::get('/ventas/pos', \App\Livewire\PointOfSale::class)->name('sales.pos'); 
    Route::get('/productsmanager', \App\Livewire\ProductManager::class)->name('productsmanager'); 
    Route::get('/exchange-rates', \App\Livewire\ExchangeRateManager::class)->name('exchange-rates'); 
    Route::get('/reports/sales', [ReportController::class, 'salesReport'])->name('reports.sales');
    Route::get('/reports/purchases', [ReportController::class, 'purchasesReport'])->name('reports.purchases');
    Route::get('/reports/inventory', [ReportController::class, 'inventoryReport'])->name('reports.inventory');
    Route::get('/reports/top-products', [ReportController::class, 'topProductsReport'])->name('reports.top-products');
    Route::get('/reports/top-selling-products', [ReportController::class, 'topSellingProductsReport'])->name('reports.top_selling_products');
    
    Route::get('/reports', function () {
        return view('reports');
    })->name('reports.index');

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

     Route::get('/ventas', function () {
        return view('ventas');
    })->name('ventas'); 
    
     Route::get('/ventas_pos', function () {
        return view('ventasPos');
    })->name('ventas_pos');  
    
    Route::get('/cashier', function () {
        return view('ventasCashier');
    })->name('cashier_sales');  
    

     Route::get('/mvt_inventory', function () {
        return view('inventory_movements');
    })->name('mvt_inventory');

     Route::get('/refunds', function () {
        return view('refunds');
    })->name('refunds');

     Route::get('/payments', function () {
        return view('payments');
    })->name('payments');     
    
    Route::get('/purchases', function () {
        return view('compras');
    })->name('compras');     


    Route::get('/management/roles-permissions', \App\Livewire\RolePermissionManager::class)
        ->middleware('can:manage_roles')
        ->name('roles.permissions.manager');

    Route::get('/sales/{sale}/ticket', [TicketController::class, 'generateTicket'])->name('sales.ticket');
    Route::get('/tickets/cashier/{sale}', [TicketController::class, 'showSaleTicketForCashier'])->name('tickets.cashier');
});