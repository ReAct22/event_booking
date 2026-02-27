<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use function Symfony\Component\Clock\now;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'slot_id',
        'quantity',
        'status'
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function slot(){
        return $this->belongsTo(TimeSlot::class, 'slot_id');
    }

    public function scopeActive($query){
        return $query->where('status', 'active');
    }

    public function scopePast($query){
        return $query->whereHas('slot', function ($q) {
            $q->where('end_time', '<', now());
        });
    }

    public function scopeUpcoming($query){
        return $query->whereHas('slot', function($q){
            $q->where('end_time', '>=', now());
        });
    }
}
