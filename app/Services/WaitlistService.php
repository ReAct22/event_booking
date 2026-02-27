<?php

namespace App\Services;

use App\Models\Waitlist;
use App\Models\TimeSlot;
use App\Models\Booking;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class WaitlistService
{
    /*
    |--------------------------------------------------------------------------
    | Join Waitlist
    |--------------------------------------------------------------------------
    */
    public function join(int $slotId, int $quantity)
    {
        if ($quantity < 1 || $quantity > 5) {
            abort(422, 'Quantity must be between 1 and 5.');
        }

        return DB::transaction(function () use ($slotId, $quantity) {

            $user = Auth::user();
            $slot = TimeSlot::lockForUpdate()->findOrFail($slotId);

            if (!$slot->isFull()) {
                abort(422, 'Slot still has available capacity.');
            }

            // Prevent double waitlist
            $alreadyWaiting = Waitlist::where('user_id', $user->id)
                ->where('slot_id', $slotId)
                ->where('status', 'waiting')
                ->exists();

            if ($alreadyWaiting) {
                abort(422, 'You are already in waitlist.');
            }

            // Prevent booking conflict
            $hasBooking = Booking::where('user_id', $user->id)
                ->where('slot_id', $slotId)
                ->where('status', 'active')
                ->exists();

            if ($hasBooking) {
                abort(422, 'You already booked this slot.');
            }

            $lastPosition = Waitlist::where('slot_id', $slotId)
                ->max('position');

            $position = $lastPosition ? $lastPosition + 1 : 1;

            return Waitlist::create([
                'user_id' => $user->id,
                'slot_id' => $slotId,
                'quantity' => $quantity,
                'position' => $position,
                'status' => 'waiting'
            ]);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Process Waitlist FIFO (Auto Promotion)
    |--------------------------------------------------------------------------
    */
    public function process(TimeSlot $slot)
    {
        return DB::transaction(function () use ($slot) {

            $slot = TimeSlot::lockForUpdate()->find($slot->id);

            $waiters = Waitlist::waiting()
                ->where('slot_id', $slot->id)
                ->orderBy('position')
                ->lockForUpdate()
                ->get();

            foreach ($waiters as $waiter) {

                if ($slot->remaining_capacity >= $waiter->quantity) {

                    Booking::create([
                        'user_id' => $waiter->user_id,
                        'slot_id' => $slot->id,
                        'quantity' => $waiter->quantity,
                        'status' => 'active'
                    ]);

                    $slot->decrement('remaining_capacity', $waiter->quantity);

                    $waiter->update(['status' => 'promoted']);

                    app(\App\Services\NotificationService::class)->create(
                        $waiter->user_id,
                        'You have been promoted from waitlist to confirmed booking.'
                    );
                }
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Cancel Waitlist
    |--------------------------------------------------------------------------
    */
    public function cancel(int $waitlistId)
    {
        $waitlist = Waitlist::findOrFail($waitlistId);

        if ($waitlist->user_id !== Auth::id()) {
            abort(403, 'Unauthorized.');
        }

        $waitlist->update(['status' => 'cancelled']);

        return ['message' => 'Waitlist cancelled'];
    }
}
