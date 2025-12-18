# Plan de Implementación de Plantilla Base 'app' en Laravel 12

**Objetivo:** Crear una plantilla base `app.blade.php` robusta y moderna, utilizando Bootstrap 5, herencia de plantillas de Blade, con sidebar, header, y un layout de dashboard que sea responsive y profesional, inspirándose en AdminLTE sin usar Service Providers ni middleware para la plantilla en sí.

---

## 1. Configuración Inicial y Dependencias (Bootstrap 5)

*   **Verificar Laravel:** Asegurarse de que el proyecto está en Laravel 12.

*   **Instalar Bootstrap 5:**
    *   Instalar Bootstrap 5 y Popper.js vía npm:
        ```bash
        npm install bootstrap@5.3.0 popper.js
        ```
    *   Configurar `resources/js/app.js` para importar Bootstrap JS:
        ```javascript
        import 'bootstrap';
        ```
    *   Configurar `resources/sass/app.scss` para importar Bootstrap SCSS:
        ```scss
        // Variables
        @import 'variables';

        // Bootstrap
        @import 'bootstrap/scss/bootstrap';

        // Custom styles
        // ...
        ```
    *   Asegurar que `vite.config.js` esté configurado para compilar SCSS y JS.

---

## 2. Creación de la Plantilla Base (`resources/views/layouts/app.blade.php`)

*   **Estructura HTML5:** Incluir el boilerplate básico (doctype, html, head, body).

*   **Meta Tags Responsivas:** Añadir meta tags para asegurar la correcta visualización en dispositivos móviles.
    ```html
    <meta name="viewport" content="width=device-width, initial-scale=1">
    ```

*   **Integrar CSS:**
    ```html
    <link rel="stylesheet" href="{{ asset('build/assets/app.css') }}">
    ```

*   **Definir Secciones Blade:**
    *   `@yield('title')` para el título de la página.
    *   `@yield('styles')` en el `head` para CSS específico de la página.
    *   `@yield('header')` para el componente del header.
    *   `@yield('sidebar')` para el componente del sidebar.
    *   `@yield('content')` para el contenido principal de la página (el "dashboard").
    *   `@yield('scripts')` al final del `body` para JS específico.

*   **Estructura General del Layout:** Utilizar clases de Bootstrap 5 para un layout flexible:
    *   Contenedor principal (`d-flex flex-column min-vh-100` para ocupar toda la altura).
    *   Un `div` para el header (`sticky-top` para fijarlo).
    *   Un `div` principal para el contenido y sidebar (`d-flex flex-grow-1` para que crezca y ocupe el espacio restante).
    *   Dentro de este, un `div` para el sidebar y otro para el contenido principal.
    *   Clases de grid de Bootstrap (`col-md-X`, `col-lg-Y`) para definir el ancho del sidebar y el contenido en diferentes tamaños de pantalla, y ocultar/mostrar el sidebar según sea necesario (e.g., `d-none d-md-block` para el sidebar en desktop, y un botón para toggler un offcanvas en móvil).

---

## 3. Componente del Header (`resources/views/partials/header.blade.php`)

*   **Creación:** Crear el archivo `resources/views/partials/header.blade.php`.

*   **Contenido:**
    *   Barra de navegación con la marca/nombre del sitio.
    *   Un botón (`navbar-toggler`) para el sidebar responsivo (offcanvas).
    *   Posibles elementos de usuario (ej. dropdown de perfil, logout) utilizando componentes de navbar y dropdown de Bootstrap 5.
    *   Estilo moderno y profesional (ej. `bg-dark`, `navbar-dark`, `shadow-sm`).

---

## 4. Componente del Sidebar (`resources/views/partials/sidebar.blade.php`)

*   **Creación:** Crear el archivo `resources/views/partials/sidebar.blade.php`.

*   **Contenido:**
    *   Menú de navegación (AdminLTE-like) con enlaces a diferentes secciones (ej. Dashboard, Usuarios, Productos).
    *   Utilizar listas (`ul`, `li`) y enlaces (`a`) de HTML, aplicando clases de Bootstrap para el estilo.
    *   Implementar un menú multinivel básico con JavaScript para toggler submenús (si es necesario).
    *   **Responsividad:** Utilizar un offcanvas de Bootstrap para el sidebar en dispositivos móviles, que se activa con el botón del header. En pantallas grandes, el sidebar estará visible y fijo.

---

## 5. Vista del Dashboard (`resources/views/dashboard.blade.php`)

*   **Creación:** Crear el archivo `resources/views/dashboard.blade.php`.

*   **Extender Layout:**
    ```blade
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
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Card Título 1</h5>
                            <p class="card-text">Contenido de la tarjeta 1.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Card Título 2</h5>
                            <p class="card-text">Contenido de la tarjeta 2.</p>
                        </div>
                    </div>
                </div>
                <!-- Más tarjetas o elementos del dashboard -->
            </div>
        </div>
    @endsection
    ```

*   **Contenido del Dashboard:** Utilizar componentes de Bootstrap (cards, grid system, etc.) para crear una disposición de ejemplo que sea profesional y responsive.

---

## 6. Enrutamiento (`routes/web.php`)

*   Definir una ruta para el dashboard que cargue la vista `dashboard.blade.php`.
*   ```php
    use Illuminate\Support\Facades\Route;

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
    ```

---

## 7. Estilización y JavaScript Adicional

*   **`resources/sass/_variables.scss`:** Para personalizar colores y otras variables de Bootstrap.
*   **`resources/sass/app.scss`:** Para estilos personalizados y sobrescrituras CSS que no sean de Bootstrap.
*   **`resources/js/app.js`:** Para inicializar componentes de Bootstrap que requieran JS (ej. tooltips, dropdowns) y cualquier lógica de JS personalizada para el sidebar (ej. alternar visibilidad en desktop, offcanvas en móvil).

---

## 8. Verificación y Pruebas

*   **Compilar Assets:** `npm run dev` (o `npm run build` para producción).
*   **Servir Aplicación:** `php artisan serve`.
*   **Pruebas Manuales:**
    *   Navegar al `/dashboard`.
    *   Verificar que el header y sidebar se muestran correctamente.
    *   Probar la responsividad del layout redimensionando la ventana del navegador o utilizando las herramientas de desarrollo del navegador para simular diferentes dispositivos.
    *   Verificar que los estilos sean modernos y profesionales.
