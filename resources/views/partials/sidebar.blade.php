<ul class="nav flex-column">
    <li class="nav-item">
        <a class="nav-link text-white active" aria-current="page" href="/dashboard">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
    </li>
    
    <li class="nav-item mt-2">
        <h6 class="text-secondary ps-3">Gestión</h6>
    </li>
    <li class="nav-item">
        <a class="nav-link text-white" href="{{ route('userscrud') }}">
            <i class="bi bi-people"></i> Usuarios
        </a>
    </li>
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
    <li class="nav-item">
        <a class="nav-link text-white" href="{{ route('categories') }}">
            <i class="bi bi-tags"></i> Categorías
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link text-white" href="{{ route('unit-measures') }}">
            <i class="bi bi-rulers"></i> Unidades de Medida
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link text-white" href="{{ route('products') }}">
            <i class="bi bi-box"></i> Productos
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link text-white" href="{{ route('clientes') }}">
            <i class="bi bi-person-badge"></i> Clientes
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link text-white" href="{{ route('proveedores') }}">
            <i class="bi bi-truck"></i> Proveedores
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link text-white" href="{{ route('ventas') }}">
            <i class="bi bi-truck"></i> Ventas
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link text-white" href="{{ route('mvt_inventory') }}">
            <i class="bi bi-arrow-left-right"></i> Movimientos de Inventario
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link text-white" href="{{ route('refunds') }}">
            <i class="bi bi-arrow-left-right"></i> Devoluciones
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link text-white" href="{{ route('payments') }}">
            <i class="bi bi-credit-card"></i> Pagos
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link text-white" href="{{ route('compras') }}">
            <i class="bi bi-credit-card"></i> Compras
        </a>
    </li>

    <li class="nav-item mt-2">
        <h6 class="text-secondary ps-3">Configuración</h6>
    </li>
    <li class="nav-item">
        <a class="nav-link text-white" href="{{ route('exchange_rates') }}">
            <i class="bi bi-currency-exchange"></i> Tasas de Cambio
        </a>
    </li>
</ul>