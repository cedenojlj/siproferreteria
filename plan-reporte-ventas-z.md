# Plan de Acción: Implementación de Reporte de Ventas (Cierre de Caja - Estilo Z)

El objetivo es crear un reporte que resuma todas las transacciones financieras desde el último cierre hasta el momento actual, similar a un "Corte Z" de una máquina fiscal. Este proceso es de solo lectura sobre las ventas pasadas, pero creará un registro histórico de cada cierre.

---

### **Fase 1: Análisis y Diseño de la Base de Datos**

Para mantener un historial de cada cierre de caja, es fundamental crear una nueva tabla. Esto nos permitirá auditar los cierres pasados y evitar recalcular todo cada vez.

1.  **Crear una nueva migración para la tabla `caja_cierres` (Cierres de Caja):**
    ```bash
    php artisan make:migration create_caja_cierres_table
    ```

2.  **Definir el esquema en el archivo de migración:** Esta tabla almacenará la "fotografía" de las métricas al momento del cierre.

    ```php
    Schema::create('caja_cierres', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained('users'); // El usuario que realizó el cierre
        $table->foreignId('company_id')->constrained('companies'); // Consistente con tu schema
        $table->timestamp('fecha_cierre'); // Momento exacto del cierre
        $table->timestamp('rango_inicio'); // Fecha/hora de inicio del período que cubre el reporte
        $table->timestamp('rango_fin');    // Fecha/hora de fin del período
        
        // Totales consolidados
        $table->decimal('total_ventas_bruto', 15, 2);
        $table->decimal('total_devoluciones', 15, 2);
        $table->decimal('total_ventas_neto', 15, 2);
        $table->decimal('total_impuestos', 15, 2); // Si manejas impuestos
        
        // Desglose por método de pago (flexible)
        $table->json('totales_por_metodo'); // Ej: {"efectivo": 1500.00, "tarjeta": 3200.50}

        $table->integer('numero_transacciones');
        $table->timestamps();
    });
    ```

3.  **Crear el Modelo `CajaCierre`:**
    ```bash
    php artisan make:model CajaCierre
    ```
    Añade el `BelongsToCompany` trait y las relaciones correspondientes (`belongsTo(User::class)`).

---

### **Fase 2: Lógica del Backend (El Corazón del Reporte)**

Crearemos una clase de servicio para encapsular la lógica de cálculo. Esto mantiene nuestros componentes de Livewire y controladores limpios.

1.  **Crear un `ReporteCajaService`:**
    *   Crea el directorio `app/Services` si no existe.
    *   Crea un nuevo archivo `app/Services/ReporteCajaService.php`.

2.  **Implementar el método `generarReporte()` en el servicio:**
    *   Este método recibirá el `userId` y `companyId`.
    *   **Paso 1: Determinar el rango de fechas.**
        *   Buscar el último cierre para la compañía: `CajaCierre::where('company_id', $companyId)->latest('fecha_cierre')->first()`.
        *   La `fecha_inicio` será la `fecha_cierre` del último reporte. Si no hay reportes, será la fecha de la primera venta registrada.
        *   La `fecha_fin` será el momento actual (`now()`).
    *   **Paso 2: Realizar las consultas.**
        *   `Ventas`: Obtener ventas (`Sale`) en el rango de fechas para calcular `total_ventas_bruto` y `numero_transacciones`.
        *   `Pagos`: Obtener pagos (`Payment`) en el rango de fechas y agrupar por `payment_method` para obtener `totales_por_metodo`.
        *   `Devoluciones`: Obtener devoluciones (`Refund`) para calcular `total_devoluciones`.
    *   **Paso 3: Calcular los totales.**
        *   `total_ventas_neto = total_ventas_bruto - total_devoluciones`.
    *   **Paso 4: Guardar el registro.**
        *   Crear una nueva instancia de `CajaCierre` con todos los datos calculados y guardarla en la base de datos.
    *   **Paso 5: Devolver los datos.**
        *   Retornar el objeto `CajaCierre` recién creado.

---

### **Fase 3: Integración en el Frontend con Livewire**

Crearemos un componente de Livewire dedicado para manejar la interacción del usuario.

1.  **Crear el componente `CierreCaja`:**
    ```bash
    php artisan make:livewire Reportes/CierreCaja
    ```
    Esto creará `app/Livewire/Reportes/CierreCaja.php` y `resources/views/livewire/reportes/cierre-caja.blade.php`.

2.  **Lógica del Componente (`CierreCaja.php`):**
    *   Tendrá una propiedad pública `$ultimoCierre` para guardar el resultado del último cierre realizado.
    *   Tendrá un método `realizarCierre()`:
        *   Este método estará protegido por un Gate o middleware de permisos (ver Fase 4).
        *   Instanciará y llamará a `ReporteCajaService->generarReporte()`.
        *   Almacenará el resultado en `$ultimoCierre`.
        *   Emitirá un evento para el frontend (ej. `dispatch('cierre-realizado', '¡Cierre de caja completado con éxito!')`).
    *   Tendrá un método `descargarPdf($cierreId)` para generar el comprobante.

3.  **Vista del Componente (`cierre-caja.blade.php`):**
    *   **Botón principal:** "Realizar Cierre de Caja". Este botón usará `wire:click="realizarCierre"` y `wire:loading.attr="disabled"`.
    *   **Modal de confirmación:** Es CRUCIAL mostrar un modal que pregunte "¿Está seguro de que desea realizar el cierre de caja? Esta acción no se puede deshacer." para evitar clics accidentales.
    *   **Área de resultados:** Después de realizar el cierre, esta sección (que al inicio está oculta) mostrará los detalles del `$ultimoCierre` en un formato claro y legible.
    *   **Botón de descarga:** Junto a los resultados, un botón "Descargar PDF" que llame a `wire:click="descargarPdf({{ $ultimoCierre->id }})"`.

4.  **Integrar en `payments.blade.php`:**
    *   En un lugar apropiado dentro de `resources/views/payments.blade.php`, simplemente renderiza el nuevo componente:
        ```html
        <div>
            <h3 class="text-lg font-medium text-gray-900">Cierre de Caja Diario (Reporte Z)</h3>
            <p class="mt-1 text-sm text-gray-600">
                Genere el reporte de cierre para consolidar todas las transacciones desde el último corte.
            </p>
            @livewire('reportes.cierre-caja')
        </div>
        ```
---

### **Fase 4: Generación de PDF y Seguridad**

1.  **Instalar `laravel-dompdf`:** Si aún no lo tienes, es el estándar para generar PDFs desde vistas Blade.
    ```bash
    composer require barryvdh/laravel-dompdf
    ```

2.  **Crear la vista del PDF:**
    *   Crea `resources/views/pdfs/reporte-cierre-caja.blade.php`.
    *   Diseña esta vista como un ticket o un reporte formal, mostrando todos los datos del `CajaCierre` que se le pasarán como variable.

3.  **Implementar la descarga en `CierreCaja.php`:**
    ```php
    use Barryvdh\DomPDF\Facade\Pdf;

    public function descargarPdf($cierreId)
    {
        $cierre = CajaCierre::findOrFail($cierreId);
        // Aquí podrías añadir una capa de seguridad para asegurar que el usuario tiene permiso
        
        $pdf = Pdf::loadView('pdfs.reporte-cierre-caja', ['cierre' => $cierre]);
        
        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->stream();
        }, 'reporte-cierre-'.$cierre->fecha_cierre->format('Y-m-d').'.pdf');
    }
    ```

4.  **Seguridad y Permisos:**
    *   Basado en tu archivo `ROLES_Y_PERMISOS.md`, esta es una acción sensible.
    *   **Define un nuevo permiso:** `generar_reporte_z` o `realizar_cierre_caja`.
    *   En `AuthServiceProvider`, define un Gate: `Gate::define('realizar-cierre-caja', function (User $user) { return $user->hasPermissionTo('realizar_cierre_caja'); });`.
    *   Protege la funcionalidad:
        *   En el método `realizarCierre()` del componente Livewire: `$this->authorize('realizar-cierre-caja');`.
        *   En la vista del componente, puedes ocultar el botón si el usuario no tiene permiso: `@can('realizar-cierre-caja') ... @endcan`.
    
---
---

### **Fase 5: Addendum y Ajustes Finales (Actualización del Plan)**

Basado en la evolución de los requerimientos, se realizarán los siguientes ajustes al plan original:

1.  **Cambio de Ubicación del Componente:**
    *   **Acción:** En lugar de integrar el componente en `payments.blade.php`, se agregará en la vista principal de reportes: `resources/views/reports.blade.php`.
    *   **Justificación:** Centraliza todas las acciones de generación de reportes en un único módulo, mejorando la coherencia de la interfaz de usuario.
    *   **Implementación:** Añadir la siguiente sección dentro del `card-body` en `reports.blade.php`, siguiendo la estructura de los otros reportes:

    ```html
    {{-- Reporte de Cierre de Caja (Corte Z) --}}
    <div class="report-section border-top mt-3 pt-3">
        <h5 class="mb-3">Cierre de Caja Diario (Reporte Z)</h5>
        <p>Genere el reporte de cierre para consolidar todas las transacciones desde el último corte hasta el momento actual. Esta acción registrará el cierre y permitirá la descarga de un comprobante.</p>
        @livewire('reportes.cierre-caja')
    </div>
    ```

2.  **Ajuste en la Lógica de Seguridad y Permisos:**
    *   **Acción:** Se elimina la necesidad de crear y verificar el permiso específico `realizar_cierre_caja`.
    *   **Justificación:** Por definición del negocio, cualquier usuario con acceso al módulo de "Reportes" tendrá implícitamente la autorización para realizar un cierre de caja. La seguridad se gestiona a nivel de ruta (acceso a la página de reportes), no a nivel de componente.
    *   **Implementación:**
        *   Eliminar la definición del Gate `realizar-cierre-caja` del `AuthServiceProvider`.
        *   Eliminar la autorización (`$this->authorize(...)`) del método `realizarCierre()` en el componente `CierreCaja.php`.
        *   Quitar cualquier directiva `@can` que envuelva la renderización del componente en la vista. El acceso a la página de reportes es suficiente.