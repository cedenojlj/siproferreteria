### **Plan de Desarrollo: Componente de Caja (Cashier)**

---

#### **Fase 1: Análisis y Planificación**

*   **Objetivo:** Comprender a fondo la arquitectura actual y definir la estructura del nuevo componente.
*   **Acciones:**
    1.  **Estudio del Código Existente:**
        *   **Modelos:** Analizaré las relaciones y atributos de los modelos `Sale`, `SaleItem`, `Product`, `InventoryMovement`, y `Customer`. Es vital entender cómo se conectan.
        *   **Componentes Livewire:** Revisaré `SaleCrud.php` y `PointOfSale.php` (que genera la vista `ventasPos`) para entender la lógica de negocio actual para la creación, actualización, finalización de ventas y manejo de inventario.
        *   **Servicios:** Inspeccionar `ThermalPrinterService.php` para ver cómo se gestiona la impresión de tickets.
        *   **Rutas y Vistas:** Examinar `routes/web.php` y los archivos `.blade.php` asociados para entender el flujo de navegación y la estructura de la UI.
    2.  **Diseño del Componente:**
        *   **Nombre:** Se creará un nuevo componente Livewire llamado `CashierSales`. Este nombre es claro y representa su propósito.
        *   **UI/UX:** El componente tendrá dos estados principales:
            1.  **Vista de Lista:** Una tabla que muestra las ventas con estado `pendiente`. Incluirá columnas clave (ID Venta, Cliente, Vendedor, Total, Fecha) y botones de acción ("Cobrar/Editar", "Eliminar").
            2.  **Vista de Edición/Cobro:** Un modal o sección que se activa al presionar "Cobrar/Editar". Esta interfaz será una adaptación mejorada de la vista `ventasPos`, permitiendo la modificación de la venta antes de finalizarla.

---

#### **Fase 2: Creación e Integración del Componente Base**

*   **Objetivo:** Generar los archivos necesarios para el nuevo componente y hacerlo accesible en la aplicación.
*   **Acciones:**
    1.  **Crear Componente Livewire:** Ejecutaré el comando para crear el componente y su vista.
        ```bash
        php artisan make:livewire CashierSales
        ```
    2.  **Crear Ruta:** Añadiré una nueva ruta en `routes/web.php` para que el componente sea accesible a través de una URL, por ejemplo, `/cashier`. Se protegerá con el middleware adecuado para asegurar que solo los usuarios con el rol de cajero puedan acceder.
        ```php
        // En routes/web.php
        Route::get('/cashier', App\Http\Livewire\CashierSales::class)->name('cashier.sales')->middleware('auth', 'role:cajero');
        ```
    3.  **Crear Vista y Enlace de Navegación:**
        *   Crearé el archivo `resources/views/livewire/cashier-sales.blade.php`.
        *   Añadiré un enlace en el menú de navegación (probablemente en `resources/views/layouts/partials/sidebar.blade.php` o similar) para que los usuarios puedan acceder a la nueva pantalla.

---

#### **Fase 3: Implementación de Funcionalidades Clave**

*   **Objetivo:** Desarrollar el núcleo de la lógica del componente según los requisitos.
*   **Acciones:**
    1.  **Listar Ventas Pendientes (Requisito 2):**
        *   En el método `render()` de `CashierSales.php`, realizaré una consulta para obtener únicamente las ventas con `status = 'pendiente'`.
        *   La vista `cashier-sales.blade.php` renderizará esta información en una tabla, inspirada en `sale-crud.blade.php`.
    2.  **Modal de Edición y Actualización (Requisitos 3 y 6):**
        *   Implementaré una función `editSale($saleId)` que cargará los datos de la venta seleccionada en las propiedades públicas del componente.
        *   El formulario de edición (similar a `ventasPos`) permitirá:
            *   Añadir y eliminar productos (`SaleItem`).
            *   Actualizar cantidades y precios.
            *   Cambiar el método de pago (`payment_method`), tipo (`payment_type`), y moneda (`payment_currency`).
    3.  **Lógica de Finalización de Venta (Requisitos 4 y 5):**
        *   Crearé un método `finalizeSale()`. Este método será transaccional para garantizar la integridad de los datos (`DB::transaction(...)`).
        *   **Si el estado se cambia a `completed`:**
            *   **Actualización de Inventario:** Iterará sobre cada `SaleItem` en la venta, disminuirá el `stock` del `Product` correspondiente.
            *   **Registro de Movimiento:** Creará un registro en la tabla `inventory_movements` por cada producto vendido para llevar un historial claro.
            *   **Actualización de la Venta:** Guardará el estado final de la venta y sus ítems.
        *   Se mostrará una notificación de éxito. La venta finalizada desaparecerá de la lista de pendientes.
    4.  **Impresión de Ticket (Requisito 4):**
        *   Añadiré un botón "Imprimir Ticket".
        *   Este botón llamará a un método `printTicket($saleId)` en el componente, que reutilizará la lógica del `ThermalPrinterService.php` para generar e imprimir el recibo de la venta.
    5.  **Eliminación de Venta (Requisito 7):**
        *   El botón "Eliminar" llamará a un método `deleteSale($saleId)`.
        *   Este método verificará que la venta aún esté en estado `pendiente`.
        *   Dentro de una transacción, eliminará primero los `SaleItem` asociados y luego la `Sale` principal para evitar problemas de integridad referencial.

---

#### **Fase 4: Pruebas y Verificación**

*   **Objetivo:** Asegurar que el componente funcione correctamente y sin errores en todos los escenarios.
*   **Acciones:**
    1.  **Pruebas Manuales del Flujo Completo:**
        *   **Escenario 1 (Feliz):**
            *   Un usuario "Vendedor" crea una venta y la deja como `pendiente`.
            *   Un usuario "Cajero" inicia sesión, ve la venta en la nueva interfaz.
            *   El cajero edita la venta, añade un producto y finaliza el pago.
            *   **Verificar:** La venta cambia a `completed`, el stock del producto se reduce y se crea el movimiento de inventario. La venta ya no aparece en la lista de pendientes.
        *   **Escenario 2 (Eliminación):**
            *   Un vendedor crea una venta. El cliente se arrepiente.
            *   El cajero busca la venta pendiente y la elimina.
            *   **Verificar:** La venta y sus ítems se eliminan de la base de datos correctamente.
    2.  **Pruebas de Componentes (Opcional, pero recomendado):**
        *   Crearé pruebas automatizadas (Pest/PHPUnit) para el componente `CashierSales` para simular estas acciones y validar los cambios en la base de datos de forma automática.
