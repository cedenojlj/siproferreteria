# Plan de Implementación: Componente de Punto de Venta (POS)

El objetivo es construir un componente de una sola página, altamente interactivo, que sirva como interfaz principal para la creación de ventas, utilizando **Livewire**.

---

### **Fase 1: Fundamentos y Backend**

En esta fase, prepararemos todo lo necesario en el lado del servidor antes de construir la interfaz.

1.  **Creación del Componente Livewire Principal:**
    *   Utilizaremos Artisan para crear el componente central que orquestará todo el proceso.
    *   **Comando:** `php artisan make:livewire PointOfSale`
    *   Esto creará `app/Livewire/PointOfSale.php` y `resources/views/livewire/point-of-sale.blade.php`.

2.  **Ruta y Vista Contenedora:**
    *   Definiremos una nueva ruta en `routes/web.php` para acceder al TPV.
    *   **Ruta:** `Route::get('/ventas/pos', \App\Http\Livewire\PointOfSale::class)->name('sales.pos');`
    *   Esta ruta renderizará directamente nuestro componente Livewire dentro del layout principal de la aplicación.

3.  **Análisis y Refuerzo de Modelos/Relaciones:**
    *   Asegurarnos de que las relaciones Eloquent están correctamente definidas:
        *   `Sale` tiene muchos `SaleItem`.
        *   `SaleItem` pertenece a un `Product`.
        *   `Sale` pertenece a un `Customer` y a un `User`.
        *   `Product` tiene un stock (`stock` o `quantity` en la tabla `products`).
    *   Crearemos los métodos necesarios en los modelos si aún no existen.

### **Fase 2: Diseño de la Interfaz de Usuario (UI) con Livewire**

Nos enfocaremos en el archivo `point-of-sale.blade.php` para maquetar la interfaz. Usaremos un diseño de dos columnas para una ergonomía óptima.

*   **Columna Izquierda (70% del ancho):**
    1.  **Búsqueda de Productos:** Un campo de texto (`wire:model.live="productSearch"`) para buscar productos por código de barras o nombre.
    2.  **Lista de Productos Agregados (Carrito):** Una tabla que mostrará los productos seleccionados. Columnas: Producto, Cantidad (editable), Precio Unitario, Subtotal. Incluirá un botón para eliminar cada ítem.

*   **Columna Derecha (30% del ancho):**
    1.  **Sección de Cliente:**
        *   Un campo de texto (`wire:model.live="customerSearch"`) para buscar clientes por nombre o RIF.
        *   Un área para mostrar los datos del cliente seleccionado.
        *   Un botón "Crear Nuevo Cliente" que abrirá un modal.
    2.  **Resumen de la Venta:** Subtotal, Impuestos (IVA), y Total.
    3.  **Acciones:**
        *   Un botón "Finalizar Venta" (deshabilitado hasta que se seleccione un cliente y haya al menos un producto).
        *   Un botón "Cancelar Venta".

### **Fase 3: Lógica del Componente Livewire (`PointOfSale.php`)**

Aquí es donde reside la lógica que responde a las interacciones del usuario.

1.  **Propiedades Públicas (Estado del Componente):**
    *   `public $customerSearch = '';`
    *   `public $customers = [];`
    *   `public $selectedCustomerId;`
    *   `public $productSearch = '';`
    *   `public $saleItems = [];` // Array con los productos del carrito
    *   `public $subtotal = 0, $tax = 0, $total = 0;`

2.  **Manejo de Clientes (Requisito 1):**
    *   Un método `updatedCustomerSearch()` que se ejecuta automáticamente cuando `$customerSearch` cambia. Hará una consulta a la base de datos y llenará el array `$customers`.
    *   Un método `selectCustomer($id)` para asignar el cliente a la venta.
    *   Implementaremos un modal (usando Alpine.js) para el formulario de creación de cliente. Un método `storeCustomer()` en el componente se encargará de validar y guardar el nuevo cliente.

3.  **Manejo de Productos (Requisitos 2 y 3):**
    *   Un método `addProduct($productId)`:
        *   **Verificación de Inventario:** Antes de agregar, consultará el stock del producto. Si es insuficiente, mostrará una notificación de error (`session()->flash()`).
        *   Si hay stock, lo añade al array `$saleItems`. Si ya existe, incrementa su cantidad.
        *   Llamará a un método `calculateTotals()` para actualizar el resumen.
    *   Métodos `updateQuantity($index, $quantity)` y `removeItem($index)` para manipular el carrito.

4.  **Finalización de la Venta:**
    *   Un método `finalizeSale()`:
        *   Utilizará una **transacción de base de datos** (`DB::transaction()`) para garantizar la atomicidad y la integridad de la operación.
        *   **Pasos críticos dentro de la transacción:**
            1.  Crear el registro principal en la tabla `sales`.
            2.  Iterar sobre `$saleItems` y crear los registros correspondientes en `sale_items`.
            3.  **Actualización de Inventario:** Por cada `SaleItem` creado, se debe actualizar de forma inmediata el stock del `Product` asociado, decrementando la cantidad vendida.
            4.  **Registro de Movimiento de Inventario:** Simultáneamente a la actualización del stock, se debe crear un registro en la tabla `inventory_movements` para cada producto vendido. Este registro debe reflejar el tipo de movimiento ('Venta'), la cantidad, y el ID del producto, garantizando así una trazabilidad completa.
        *   Al finalizar con éxito la transacción, se limpiará el estado del componente (`reset()`) y se emitirá un evento al navegador para la impresión del ticket.

### **Fase 4: Impresión del Ticket Térmico (Requisito 4)**

1.  **Ruta y Controlador para el Ticket:**
    *   Crearemos una ruta: `GET /tickets/sale/{sale}`.
    *   El controlador (`ReportController@showSaleTicket`) cargará los datos y los pasará a una vista Blade.

2.  **Vista Blade para el Ticket:**
    *   Crearemos una vista (`resources/views/reports/sale_ticket.blade.php`) con HTML y CSS simple para impresoras térmicas.
    *   **Integración:** Se deberá analizar y utilizar el servicio existente `ThermalPrinterService.php` dentro del controlador para formatear los datos.

3.  **Disparar la Impresión (JavaScript):**
    *   En el método `finalizeSale()` de Livewire, emitiremos un evento: `$this->dispatch('print-ticket', saleId: $newSale->id);`
    *   En el layout principal, un script de JavaScript escuchará este evento, abrirá una nueva ventana con la URL del ticket y ejecutará `window.print()`.
    *   **Script:**
        ```javascript
        <script>
            document.addEventListener('livewire:initialized', () => {
                Livewire.on('print-ticket', (event) => {
                    const saleId = event.saleId;
                    const url = `/tickets/sale/${saleId}`;
                    const printWindow = window.open(url, '_blank', 'height=600,width=400');
                    printWindow.onload = function() {
                        printWindow.print();
                    };
                });
            });
        </script>
        ```
---

### **Anexo Técnico: Patrón de Actualización de Inventario (Basado en `PurchaseCrud.php`)**

El análisis del componente `PurchaseCrud.php` revela un patrón robusto y centralizado para la gestión de inventario, el cual debe ser replicado en el componente de ventas.

1.  **Método Centralizador:** Existe un método privado `recordInventoryMovement()`. Esta es la práctica a seguir.
    *   **Responsabilidad 1: Crear Registro de Movimiento:** Crea un registro en la tabla `inventory_movements` detallando el `product_id`, `user_id`, `movement_type` ('in' para compras), `quantity`, el ID y tipo del modelo de referencia (`purchase`).
    *   **Responsabilidad 2: Actualizar Stock del Producto:** Utiliza los métodos `increment()` o `decrement()` de Eloquent sobre la columna de stock del modelo `Product`.

2.  **Ejecución Transaccional:** Todas las operaciones de guardado, actualización o borrado que afectan al inventario están envueltas en un `DB::transaction()`. Esto asegura la atomicidad: o todas las operaciones (crear compra, crear ítems, actualizar inventario, registrar movimiento) tienen éxito, o todas se revierten.

3.  **Invocación Condicional:** La lógica de inventario solo se ejecuta cuando el estado de la compra lo justifica (ej. `status === 'received'`).

**Aplicación al Módulo de Ventas (`PointOfSale`):**

*   Se creará un método privado `recordInventoryMovement()` en el componente `PointOfSale`.
*   Este método será invocado dentro de la transacción del método `finalizeSale()` por cada producto vendido.
*   El `movement_type` será `'out'`.
*   La `reference_type` será `'sale'`.
*   Se usará `$product->decrement('current_stock', $quantity)`.
