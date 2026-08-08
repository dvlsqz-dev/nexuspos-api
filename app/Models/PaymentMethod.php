<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PaymentMethod extends Model
{
    use HasFactory, BelongsToTenant, SoftDeletes;

    protected $fillable = ['name', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];
}
