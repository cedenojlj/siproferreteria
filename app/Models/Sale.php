<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'invoice_number',
        'customer_id',
        'seller_id',
        'cashier_id',
        'payment_currency',
        'payment_method',
        'payment_type',
        'exchange_rate',        
        'subtotal_usd',
        'tax',        
        'total_usd',
        'pending_balance',
        'status',
        'notes',
    ];

    protected $casts = [
        'exchange_rate' => 'decimal:2',        
        'subtotal_usd' => 'decimal:2',
        'tax' => 'decimal:2',        
        'total_usd' => 'decimal:2',
        'pending_balance' => 'decimal:2',
    ];

    protected $appends = [
        'is_credit',
        'is_paid',
    ];

    public function getIsCreditAttribute(): bool
    {
        return $this->payment_type === 'credit';
    }

    public function getIsPaidAttribute(): bool
    {
        return $this->pending_balance <= 0;
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class);
    }

    //user relation
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }
}
