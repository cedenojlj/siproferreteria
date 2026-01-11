<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('invoice_number', 50);
            $table->foreignId('customer_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('seller_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('cashier_id')->constrained('users')->onDelete('cascade');
            $table->enum('payment_currency', ['BS', 'USD'])->default('BS');
            $table->enum('payment_method', ['EFECTIVO', 'DEBITO','TRANSFERENCIA', 'PAGO_MOVIL', 'ZELLE', 'BANESCO_PANAMA', 'OTRO'])->default('DEBITO');
            $table->enum('payment_type', ['EFECTIVO', 'CREDITO'])->default('EFECTIVO');
            $table->decimal('exchange_rate', 10, 4);            
            $table->decimal('subtotal_usd', 12, 2)->default(0.00);
            $table->decimal('tax', 12, 2); 
            $table->decimal('tax_porcentaje', 12, 2)->default(16.00);           
            $table->decimal('total_usd', 12, 2)->default(0.00);
            $table->decimal('pending_balance', 12, 2)->default(0.00);
            $table->enum('status', ['pending', 'completed', 'cancelled', 'credit'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'invoice_number']);
            $table->index(['company_id', 'customer_id', 'seller_id', 'created_at']);
            $table->index('status');
            $table->index('created_at');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
