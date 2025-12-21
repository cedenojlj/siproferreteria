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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained()->onDelete('restrict');
            $table->foreignId('unit_measure_id')->constrained()->onDelete('restrict');
            $table->string('barcode', 100)->unique()->nullable();
            $table->string('name', 200);
            $table->text('description')->nullable();
            $table->string('brand', 100)->nullable();
            $table->string('model', 100)->nullable();
            $table->decimal('base_price', 12, 2);
            $table->decimal('usd_price', 12, 2);
            $table->decimal('cost', 12, 2);
            $table->integer('min_stock')->default(10);
            $table->integer('current_stock')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['company_id', 'category_id', 'barcode']);
            $table->index('is_active');
            $table->fullText(['name', 'description']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
