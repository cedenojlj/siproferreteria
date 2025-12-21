<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'sale_id',
        'customer_id',
        'amount_local',
        'amount_usd',
        'payment_method',
        'reference',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'amount_local' => 'decimal:2',
        'amount_usd' => 'decimal:2',
    ];

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
}
