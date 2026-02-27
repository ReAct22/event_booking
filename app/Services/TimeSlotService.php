<?php

namespace App\Services;

use App\Models\Event;
use App\Models\TimeSlot;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TimeSlotService{
    public function create(Event $event, array $data){
        $this->authorizeOwner($event);
        if($data['start_time'] >= $data['end_time']){
            abort(422, 'Start time must be before end time');
        }

        return $event->timeSlots()->create([
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'capacity' => $data['capacity'],
            'remaining_capacity' => $data['capacity']
        ]);
    }

    public function update(TimeSlot $slot, array $data){
        $this->authorizeOwner($slot->event);

        if($data['start_time'] >= $data['end_time']){
            abort(422, 'Start time must be before end time');
        }

        DB::transaction(function () use ($slot, $data){
            $slot->lockForUpdate();

            $difference = $data['capacity'] - $slot->capacity;

            $newRemaining = $slot->remaining_capacity + $difference;

            if($newRemaining < 0){
                abort(422, 'Capacity cannot be lower than total booked spots');
            }

            $slot->update([
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'capacity' => $data['capacity'],
                'remaining_capacity' => $newRemaining
            ]);
        });

        return $slot->fresh();
    }

    public function delete(TimeSlot $slot){
        $this->authorizeOwner($slot->event);
        if($slot->bookings()->where('status', 'active')->exists()){
            abort(422, 'Cannot delete slot with active bookings');
        }

        $slot->delete();
    }

    private function authorizeOwner(Event $event){
        if($event->created_by !== Auth::id()){
            abort(403, 'You are not allowed to modify this event');
        }
    }
}
