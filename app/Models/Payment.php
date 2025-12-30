<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'tran_id',
        'amount',
        'currency',
        'status',
        'payment_method',
        'bank_tran_id',
        'card_type',
        'card_no',
        'product_name',
        'product_category',
        'cus_name',
        'cus_email',
        'cus_phone',
        'cus_address',
        'cus_city',
        'cus_country',
        'cus_postcode',
        'success_url',
        'fail_url',
        'cancel_url',
        'ipn_url',
        'ssl_response',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'ssl_response' => 'array',
    ];

    /**
     * Get the user that owns the payment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if payment is successful
     */
    public function isSuccessful(): bool
    {
        return $this->status === 'success';
    }

    /**
     * Check if payment is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if payment is failed
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }
}
