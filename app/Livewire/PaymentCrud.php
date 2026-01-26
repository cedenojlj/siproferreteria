<?php

namespace App\Livewire;

use App\Models\Payment;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use App\Models\Sale;
use App\Models\Customer;
use App\Models\User;
use App\Models\ExchangeRate;
use App\Models\Company;
use Barryvdh\DomPDF\Facade\Pdf;

class PaymentCrud extends Component
{
    use WithPagination;

    // Model properties
    public $payment_id, $sale_id, $customer_id, $amount_local, $amount_usd,
           $payment_method, $reference, $notes, $user_id;

    public $sale, $customer;

    // Control properties
    public $isModalOpen = false;
    public $search = '';
    public $customerSearch = '';

    // Data for dropdowns
    public $sales = [];
    // public $customers = []; // Se cargará dinámicamente
    public $users = [];

    // --- Estado para el Módulo de Abonos ---
    public $selectedCustomerId = null;
    public $creditSales = [];
    public $selectedSaleId = null;
    public ?Sale $selectedSale = null;

    // --- Formulario de Nuevo Abono ---
    public $new_payment_amount = 0;
    public $new_payment_method = 'CASH'; // Coincidir con los enums de la BD
    public $new_payment_reference = '';


    protected $rules = [
        'reference' => 'nullable|string|max:100',
        'notes' => 'nullable|string',
    ];

    public function mount()
    {
        // El mount se mantiene ligero, las cargas dinámicas van en render
    }

    public function render()
    {
        $company_id = Auth::user()->company_id;

        // Búsqueda de clientes para el dropdown
        $customers = Customer::where('company_id', $company_id)
            ->search($this->customerSearch)
            ->orderBy('name')
            ->limit(25)
            ->get(['id', 'name', 'document']);

        // Búsqueda principal de la tabla de pagos
        $payments = Payment::where('company_id', $company_id)
            ->with(['sale', 'customer', 'user'])
            ->where(function($query) {
                $query->where('reference', 'like', '%' . $this->search . '%')
                      ->orWhere('notes', 'like', '%' . $this->search . '%')
                      ->orWhereHas('sale', function($q) {
                          $q->where('invoice_number', 'like', '%' . $this->search . '%');
                      })
                      ->orWhereHas('customer', function($q) {
                          $q->search($this->search);
                      })
                      ->orWhereHas('user', function($q) {
                          $q->where('name', 'like', '%' . $this->search . '%');
                      });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.payment-crud', [
            'customers' => $customers,
            'payments' => $payments
        ]);
    }

    // --- Módulo de Abonos ---
    public function updatedSelectedCustomerId($customerId)
    {
        if ($customerId) {
            $this->creditSales = Sale::where('company_id', Auth::user()->company_id)
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
            $this->selectedSale = Sale::find($saleId);
        }
        $this->reset(['new_payment_amount', 'new_payment_reference']);
    }

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
        $exchangeRate = ExchangeRate::latest()->first()->rate ?? 1;
        $amountLocal = $amountUsd * $exchangeRate;

        // 2. Registrar el pago
        Payment::create([
            'sale_id' => $this->selectedSale->id,
            'customer_id' => $this->selectedSale->customer_id,
            'user_id' => Auth::id(),
            'company_id' => Auth::user()->company_id,
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


    // --- Métodos de CRUD originales (edit-only) ---
    public function create()
    {
        session()->flash('info', 'Utilice el módulo de "Registro de Abonos" para agregar pagos a ventas a crédito.');
        $this->closeModal();
    }

    public function openModal()
    {
        $this->isModalOpen = true;
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->resetValidation();
        $this->resetInputFields();
    }

    private function resetInputFields()
    {
        $this->reset([
            'payment_id', 'sale_id', 'customer_id', 'amount_local', 'amount_usd',
            'payment_method', 'reference', 'notes', 'user_id', 'sale', 'customer'
        ]);
    }

    public function store()
    {
        session()->flash('error', 'La creación directa de pagos no está permitida.');
        $this->closeModal();
    }

    public function edit($id)
    {
        $company_id = Auth::user()->company_id;
        $payment = Payment::where('company_id', $company_id)
                        ->with(['sale', 'customer', 'user'])
                        ->findOrFail($id);

        $this->payment_id = $id;
        $this->sale_id = $payment->sale_id;
        $this->customer_id = $payment->customer_id;
        $this->amount_local = $payment->amount_local;
        $this->amount_usd = $payment->amount_usd;
        $this->payment_method = $payment->payment_method;
        $this->reference = $payment->reference;
        $this->notes = $payment->notes;
        $this->user_id = $payment->user_id;

        $this->sale = $payment->sale;
        $this->customer = $payment->customer;

        $this->openModal();
    }

    public function update()
    {
        $company_id = Auth::user()->company_id;
        $this->validate([
            'reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        if ($this->payment_id) {
            $payment = Payment::where('company_id', $company_id)->findOrFail($this->payment_id);
            $payment->update([
                'reference' => $this->reference,
                'notes' => $this->notes,
            ]);
            
            session()->flash('message', 'Pago actualizado exitosamente.');
            $this->closeModal();
            $this->resetInputFields();
        }
    }

    public function printReceipt($paymentId)
    {
        $company = Company::find(Auth::user()->company_id);
        $payment = Payment::with(['sale', 'customer', 'user'])->find($paymentId);

        if (!$payment || $payment->company_id != $company->id) {
            session()->flash('error', 'Recibo no encontrado o no autorizado.');
            return;
        }

        // Calcular saldo anterior
        $saldoAnterior = ($payment->sale->pending_balance ?? 0) + $payment->amount_usd;

        $data = [
            'payment' => $payment,
            'company' => $company,
            'saldoAnterior' => $saldoAnterior
        ];

        $pdf = Pdf::loadView('pdfs.payment_receipt', $data);

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->stream();
        }, 'recibo-pago-' . $paymentId . '.pdf');
    }

    public function delete($id)
    {
        session()->flash('error', 'La eliminación directa de pagos no está permitida para mantener la integridad financiera.');
    }
}
