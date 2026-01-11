<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class RefundItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'refund_id',
        'sale_item_id',
        'product_id',
        'quantity',
        'unit_price_usd',
        'subtotal_usd',
        'tax_usd',      
        'item_condition',        
    ];

    protected $casts = [
        'unit_price_usd' => 'decimal:2',
        'subtotal_usd' => 'decimal:2', 
        'tax_usd' => 'decimal:2',       
    ];

    public function refund(): BelongsTo
    {
        return $this->belongsTo(Refund::class);
    }

    public function saleItem(): BelongsTo
    {
        return $this->belongsTo(SaleItem::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
