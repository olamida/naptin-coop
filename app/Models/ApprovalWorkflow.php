<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovalWorkflow extends Model
{
    protected $fillable = [
        'key',
        'name',
        'required_permission',
        'required_roles',
        'threshold_amount',
        'enabled',
    ];

    protected $casts = [
        'required_roles' => 'array',
        'threshold_amount' => 'decimal:2',
        'enabled' => 'boolean',
    ];
}
