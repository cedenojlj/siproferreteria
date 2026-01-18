<ul class="nav flex-column">
    <li class="nav-item">
        <a class="nav-link text-white active" aria-current="page" href="{{ route('home') }}">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
    </li>
    
    
    <li class="nav-item mt-2">
        <h6 class="text-secondary ps-3">Gestión</h6>
    </li>
    @can('Gestionar_usuarios')
    <li class="nav-item">
        <a class="nav-link text-white" href="{{ route('userscrud') }}">
            <i class="bi bi-people"></i> Usuarios
        </a>
    </li>
    @endcan
    
    @can('manage_roles')
    <li class="nav-item">
        <a class="nav-link text-white" href="{{ route('roles.permissions.manager') }}">
            <i class="bi bi-shield-lock"></i> Roles y Permisos
        </a>
    </li>
    @endcan
   
    <li class="nav-item mt-2">
        <h6 class="text-secondary ps-3">Catálogos</h6>
    </li>
    @can('Configurar_categorías')
    <li class="nav-item">
        <a class="nav-link text-white" href="{{ route('categories') }}">
            <i class="bi bi-tags"></i> Categorías
        </a>
    </li>
    @endcan
    @can('Configurar_unidades_de_medida')
    <li class="nav-item">
        <a class="nav-link text-white" href="{{ route('unit-measures') }}">
            <i class="bi bi-rulers"></i> Unidades de Medida
        </a>
    </li>
    @endcan
    @can('Ver_productos')
    <li class="nav-item">
        <a class="nav-link text-white" href="{{ route('products') }}">
            <i class="bi bi-box"></i> Productos
        </a>
    </li>
    @endcan
    @can('Ver_clientes')
    <li class="nav-item">
        <a class="nav-link text-white" href="{{ route('clientes') }}">
            <i class="bi bi-person-badge"></i> Clientes
        </a>
    </li>
    @endcan
    @can('Ver_proveedores')
    <li class="nav-item">
        <a class="nav-link text-white" href="{{ route('proveedores') }}">
            <i class="bi bi-truck"></i> Proveedores
        </a>
    </li>
    @endcan

    <li class="nav-item mt-2">
        <h6 class="text-secondary ps-3">Operaciones</h6>
    </li>
    @can('Ver_ventas')
    <li class="nav-item">
        <a class="nav-link text-white" href="{{ route('ventas') }}">
            <i class="bi bi-cart4"></i> Ventas
        </a>
    </li>
    @endcan
    @can('Caja')
    <li class="nav-item">
        <a class="nav-link text-white" href="{{ route('cashier_sales') }}">
            <i class="bi bi-cash-stack"></i> Caja
        </a>
    </li>
    @endcan
    @can('POS')
    <li class="nav-item">
        <a class="nav-link text-white" href="{{ route('ventas_pos') }}">
            <i class="bi bi-truck"></i> POS
        </a>
    </li>
    @endcan
    @can('Ver_movimientos')
    <li class="nav-item">
        <a class="nav-link text-white" href="{{ route('mvt_inventory') }}">
            <i class="bi bi-arrow-left-right"></i> Movimientos de Inventario
        </a>
    </li>
    @endcan
    @can('Ver_devoluciones')
    <li class="nav-item">
        <a class="nav-link text-white" href="{{ route('refunds') }}">
            <i class="bi bi-arrow-left-right"></i> Devoluciones
        </a>
    </li>
    @endcan
    @can('Ver_pagos')
    <li class="nav-item">
        <a class="nav-link text-white" href="{{ route('payments') }}">
            <i class="bi bi-credit-card"></i> Pagos
        </a>
    </li>
    @endcan
    @can('Ver_compras')
    <li class="nav-item">
        <a class="nav-link text-white" href="{{ route('compras') }}">
            <i class="bi bi-credit-card"></i> Compras
        </a>
    </li>
    @endcan

    <li class="nav-item mt-2">
        <h6 class="text-secondary ps-3">Configuración</h6>
    </li>
    @can('Configurar_tasas_de_cambio')
    <li class="nav-item">
        <a class="nav-link text-white" href="{{ route('exchange_rates') }}">
            <i class="bi bi-currency-exchange"></i> Tasas de Cambio
        </a>
    </li>
    @endcan

    <li class="nav-item mt-2">
        <h6 class="text-secondary ps-3">Reportes</h6>
    </li>
    @can('Ver_reportes_general')
    <li class="nav-item">
        <a class="nav-link text-white" href="{{ route('reports.index') }}">
            <i class="bi bi-journal-text"></i> Reportes
        </a>
    </li>
    @endcan
</ul>