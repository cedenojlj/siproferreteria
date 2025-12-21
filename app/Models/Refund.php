<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Refund extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'sale_id',
        'customer_id',
        'user_id',
        'total_amount_usd',
        'tax_returned_local',
        'status',
        'refund_method',
        'credit_note_number',
        'reason',
    ];

    protected $casts = [
        'total_amount_usd' => 'decimal:2',
        'tax_returned_local' => 'decimal:2',
    ];

    protected $appends = [
        'is_approved',
        'is_completed',
    ];

    public function getIsApprovedAttribute(): bool
    {
        return in_array($this->status, ['approved', 'completed']);
    }

    public function getIsCompletedAttribute(): bool
    {
        return $this->status === 'completed';
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function refundItems(): HasMany
    {
        return $this->hasMany(RefundItem::class);
    }
}
