<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContractStatusRequest extends Model
{
    protected $fillable = [
        'contract_id',
        'requested_by',
        'resolved_by',
        'requested_status',
        'reason',
        'rejection_reason',
        'status',
        'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    // ── Relaciones ──────────────────────────────

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function resolver()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    // ── Scopes ──────────────────────────────────

    public function scopePendiente($query)
    {
        return $query->where('status', 'pendiente');
    }

    // ── Helpers ─────────────────────────────────

    public function isPendiente(): bool
    {
        return $this->status === 'pendiente';
    }
}
