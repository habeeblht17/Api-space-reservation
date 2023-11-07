<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory, HasUuids;


    protected $fillable = [
        'user_id',
        'space_id',
        'start_time',
        'end_time',
        'canceled_at',
    ];

    //
    //Relationship
    //
    public function spaces()
    {
        return $this->hasMany(Space::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
