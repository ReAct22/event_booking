<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use PDO;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'location',
        'created_by'
    ];

    // Relationships

    public function creator(){
        return $this->belongsTo(User::class, 'created_by');
    }

    public function timeSlots(){
        return $this->hasMany(TimeSlot::class);
    }

    // Scopes

    public function scopeSearche($query, $search){
        if(!$search) return $query;

        return $query->where(function ($q) use ($search){
            $q->where('title', 'ILIKE', "%{$search}%")
            ->orWhere('description', 'ILIKE', "%{$search}%");
        });
    }
}
