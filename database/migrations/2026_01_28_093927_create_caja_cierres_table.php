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
        Schema::create('caja_cierres', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users'); // El usuario que realizó el cierre
            $table->foreignId('company_id')->constrained('companies'); // Consistente con tu schema
            $table->timestamp('fecha_cierre')->nullable(); // Momento exacto del cierre
            $table->timestamp('rango_inicio')->nullable(); // Fecha/hora de inicio del período que cubre el reporte
            $table->timestamp('rango_fin')->nullable();    // Fecha/hora de fin del período
            
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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('caja_cierres');
    }
};
