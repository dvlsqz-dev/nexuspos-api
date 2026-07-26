<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tenant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'nit',
        'legal_name',
        'business_type',
        'address',
        'phone',
        'tax_regime',
        'default_tax_rate',
        'prices_include_tax',
        'currency',
        'is_active',
        'status',
    ];

    protected $casts = [
        'prices_include_tax' => 'boolean',
        'is_active' => 'boolean',
        'default_tax_rate' => 'decimal:2',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(TenantDocument::class);
    }
}
