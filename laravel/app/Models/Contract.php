<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contract extends Model
{
    use HasFactory, SoftDeletes;

    const STATUS_ASIGNADO = 'asignado';
    const STATUS_LIMPIO = 'limpio';
    const STATUS_LLENO = 'lleno';
    const STATUS_ANULADO = 'anulado';
    const STATUS_VENTA = 'venta';

    protected $fillable = [
        'number',
        'advisor_id',
        'current_status',
        'delivered_at',
        'client_name',
        'client_document',
        'client_phone',
        'amount',
        'signed_at',
        'expires_at'
    ];

    protected $casts = [
        'delivered_at' => 'datetime',
    ];

    public function advisor()
    {
        return $this->belongsTo(User::class, 'advisor_id');
    }

    public function histories()
    {
        return $this->hasMany(ContractStatusHistory::class)->latest();
    }
    public function statusRequests()
    {
        return $this->hasMany(\App\Models\ContractStatusRequest::class);
    }
}
