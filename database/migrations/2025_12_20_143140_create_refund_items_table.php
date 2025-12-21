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
        Schema::create('refund_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('refund_id')->constrained()->onDelete('cascade');
            $table->foreignId('sale_item_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('restrict');
            $table->integer('quantity');
            $table->decimal('unit_price_usd', 12, 2);
            $table->decimal('subtotal_usd', 12, 2);
            $table->decimal('tax_local', 12, 2)->default(0.00);
            $table->text('reason')->nullable();
            $table->enum('item_condition', ['new', 'used', 'damaged', 'defective'])->default('new');
            $table->boolean('restocked')->default(false);
            $table->timestamp('restocked_at')->nullable();
            $table->timestamps();

            $table->index(['refund_id', 'product_id']);
            $table->index('restocked');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refund_items');
    }
};
