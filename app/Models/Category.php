<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'title', 'description', 'image', 'status',
    ];


    //
    //Relationship
    //
    public function spaces()
    {
        return $this->hasMany(Space::class);
    }

    //
    //Scope
    //
    public function scopeActive(Builder $query)
    {
        $query->where('status', 'active');
    }
}
