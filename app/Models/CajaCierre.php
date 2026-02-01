<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CajaCierre extends Model
{
    use HasFactory, BelongsToCompany;

    protected $fillable = [
        'user_id',
        'company_id',
        'fecha_cierre',
        'rango_inicio',
        'rango_fin',
        'total_ventas_bruto',
        'total_devoluciones',
        'total_ventas_neto',
        'total_impuestos',
        'totales_por_metodo',
        'numero_transacciones',
    ];

    protected $casts = [
        'fecha_cierre' => 'datetime',
        'rango_inicio' => 'datetime',
        'rango_fin' => 'datetime',
        'totales_por_metodo' => 'array',
    ];

    /**
     * Get the user who performed the closing.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
