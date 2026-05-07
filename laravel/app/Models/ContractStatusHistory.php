<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContractStatusHistory extends Model
{
    protected $fillable = [
        'contract_id',
        'changed_by',
        'previous_status',
        'new_status',
        'observation',
        'changed_at',
    ];

    protected $casts = [
        'changed_at' => 'datetime',
    ];

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
