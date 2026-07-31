<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Unit extends Model
{
    use BelongsToTenant, HasFactory, SoftDeletes;

    protected $fillable = ['name', 'abbreviation'];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
