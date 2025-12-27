<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'category_id',
        'unit_measure_id',
        'barcode',
        'name',
        'description',
        'brand',
        'model',
        'base_price',
        'usd_price',
        'cost',
        'min_stock',
        'current_stock',
        'is_active',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'usd_price' => 'decimal:2',
        'cost' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    protected $appends = [
        'profit_margin',
        'is_low_stock',
    ];

    // Accessors
    public function getProfitMarginAttribute(): float
    {
        if ($this->cost == 0) {
            return 0;
        }
        return (($this->base_price - $this->cost) / $this->cost) * 100;
    }

    public function getIsLowStockAttribute(): bool
    {
        return $this->current_stock <= $this->min_stock;
    }

    

    // Relaciones
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function unitMeasure(): BelongsTo
    {
        return $this->belongsTo(UnitMeasure::class);
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function purchaseItems(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function refundItems(): HasMany
    {
        return $this->hasMany(RefundItem::class);
    }

}
