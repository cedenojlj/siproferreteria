# Plan de Diseño: Módulo de Registro de Abonos a Créditos

## Objetivo:
Evolucionar `PaymentCrud.php` para convertirlo en un módulo de "Cuentas por Cobrar", permitiendo al usuario seleccionar un cliente, ver sus ventas a crédito con saldo pendiente y registrar nuevos abonos (pagos) a la venta seleccionada.

---

## Fase 1: Verificación de la Estructura de Datos

Se ha verificado la migración de la tabla `sales` y se confirma que contiene las columnas `pending_balance` y `status`, que son clave para esta implementación.

---

## Fase 2: Lógica del Componente (`PaymentCrud.php`)

1.  **Definir Estado del Componente:**
    *   Añadir propiedades para manejar la selección del usuario y el formulario de abono.

    ```php
    // --- Estado para el Módulo de Abonos ---
    public $selectedCustomerId = null;
    public $creditSales = [];
    public $selectedSaleId = null;
    public ?\App\Models\Sale $selectedSale = null;

    // --- Formulario de Nuevo Abono ---
    public $new_payment_amount = 0;
    public $new_payment_method = 'EFECTIVO';
    public $new_payment_reference = '';
    ```

2.  **Poblar Selectores Dinámicamente (Métodos `updated`):**
    *   Utilizar "Lifecycle Hooks" de Livewire para reaccionar a las selecciones del usuario.

    ```php
    public function updatedSelectedCustomerId($customerId)
    {
        if ($customerId) {
            $this->creditSales = \App\Models\Sale::where('company_id', \Illuminate\Support\Facades\Auth::user()->company_id)
                ->where('customer_id', $customerId)
                ->where('status', 'credit')
                ->where('pending_balance', '>', 0)
                ->get();
        }
        $this->reset(['selectedSaleId', 'selectedSale', 'new_payment_amount', 'new_payment_reference']);
    }

    public function updatedSelectedSaleId($saleId)
    {
        if ($saleId) {
            $this->selectedSale = \App\Models\Sale::find($saleId);
        }
        $this->reset(['new_payment_amount', 'new_payment_reference']);
    }
    ```

3.  **Crear el Método `addPayment()`:**
    *   Función principal que guarda el abono y actualiza la venta.

    ```php
    public function addPayment()
    {
        if (!$this->selectedSale) return;

        // 1. Validar
        $this->validate([
            'new_payment_amount' => 'required|numeric|min:0.01|max:' . $this->selectedSale->pending_balance,
            'new_payment_method' => 'required|string',
            'new_payment_reference' => 'nullable|string|max:100',
        ]);

        // Asumiendo que el abono se registra en USD
        $amountUsd = $this->new_payment_amount;
        $exchangeRate = \App\Models\ExchangeRate::latest()->first()->rate ?? 1;
        $amountLocal = $amountUsd * $exchangeRate;

        // 2. Registrar el pago
        \App\Models\Payment::create([
            'sale_id' => $this->selectedSale->id,
            'customer_id' => $this->selectedSale->customer_id,
            'user_id' => \Illuminate\Support\Facades\Auth::id(),
            'company_id' => \Illuminate\Support\Facades\Auth::user()->company_id,
            'amount_usd' => $amountUsd,
            'amount_local' => $amountLocal,
            'payment_method' => $this->new_payment_method,
            'reference' => $this->new_payment_reference,
            'notes' => 'Abono a factura ' . $this->selectedSale->invoice_number,
        ]);

        // 3. Actualizar la venta
        $this->selectedSale->pending_balance -= $amountUsd;
        if ($this->selectedSale->pending_balance <= 0) {
            $this->selectedSale->pending_balance = 0;
            $this->selectedSale->status = 'completed';
        }
        $this->selectedSale->save();

        // 4. Refrescar y notificar
        session()->flash('message', 'Abono registrado exitosamente.');
        $this->updatedSelectedCustomerId($this->selectedCustomerId);
    }
    ```

---

## Fase 3: Interfaz de Usuario (`payment-crud.blade.php`)

1.  **Añadir el Panel de "Registro de Abonos":**
    *   En la parte superior de la vista, añadir una nueva sección con los selectores y el formulario.

    ```html
    <div class="p-4 bg-white rounded-lg shadow-md mb-4">
        <h2 class="text-xl font-bold mb-4">Registro de Abonos a Créditos</h2>
        <!-- Selectores y formulario -->
    </div>
    ```

---

## Fase 4: Ejecución

*   Modificar el archivo `app/Livewire/PaymentCrud.php` para añadir las propiedades y métodos descritos.
*   Modificar el archivo `resources/views/livewire/payment-crud.blade.php` para añadir la nueva interfaz de usuario.
