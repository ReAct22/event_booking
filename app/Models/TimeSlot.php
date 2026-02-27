<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimeSlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'start_time',
        'end_time',
        'capacity',
        'remaining_capacity'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime'
    ];

    public function event(){
        return $this->belongsTo(Event::class);
    }

    public function bookings(){
        return $this->hasMany(Booking::class, 'slot_id');
    }

    public function waitlists(){
        return $this->hasMany(Waitlist::class, 'slot_id');
    }

    public function isFull(){
        return $this->remaining_capacity <= 0;
    }

    public function isPast(){
        return $this->end_time->isPast();
    }

    public function overlaps($start, $end){
        return $this->start_time < $end && $this->end_time > $start;
    }

}
