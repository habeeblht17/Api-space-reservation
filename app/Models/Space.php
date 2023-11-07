<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Space extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'spaces';

    protected $fillable = [
        'category_id',
        'title',
        'description',
        'rate_per_unit',
        'capacity',
        'measurement',
        'status',
        'availability',
        'image',
    ];

    //
    //Relationships
    //
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    //
    //Scope
    //
    public function scopeActive(Builder $query)
    {
        $query->where('status', 'active');
    }

    public function scopeAvailable(Builder $query)
    {
        $query->where('availability', true);
    }

    public function scopeUnavailable(Builder $query)
    {
        $query->where('availability', false);
    }
}
