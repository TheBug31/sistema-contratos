<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Operation extends Model
{
    protected $fillable = ['name', 'description', 'monthly_goal', 'contracts_goal', 'sales_goal'];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function contracts()
    {
        return $this->hasManyThrough(
            Contract::class,
            User::class,
            'operation_id', // FK en users → operations
            'advisor_id',   // FK en contracts → users
            'id',           // PK en operations
            'id'            // PK en users
        );
    }
}
