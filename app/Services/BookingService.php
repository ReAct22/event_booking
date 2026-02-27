<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\TimeSlot;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BookingService
{
    public function book(int $slotId, int $quantity)
    {
        if ($quantity < 1 || $quantity > 5) {
            abort(422, 'Booking quantity must be between 1 and 5');
        }

        return DB::transaction(function () use ($slotId, $quantity) {
            $user = Auth::user();

            $slot = TimeSlot::lockForUpdate()->findOrFail($slotId);

            if ($slot->isPast()) {
                abort(422, 'Cannot book past slot');
            }

            $alreadyBooked = Booking::where('user_id', $user->id)
                ->where('slot_id', $slotId)
                ->where('status', 'active')
                ->exists();

            if ($alreadyBooked) {
                abort(422, 'You already booked this slot');
            }

            $conflict = Booking::active()
                ->where('user_id', $user->id)
                ->whereHas('slot', function ($q) use ($slot) {
                    $q->where(function ($query) use ($slot) {
                        $query->whereBetween('start_time', [$slot->start_time, $slot->end_time])
                            ->orWhereBetween('end_time', [$slot->start_time, $slot->end_time])
                            ->orWhere(function ($inner) use ($slot) {
                                $inner->where('start_time', '<=', $slot->start_time)
                                    ->where('end_time', '>=', $slot->end_time);
                            });
                    });
                })->exists();

            if ($conflict) {
                abort(422, 'You have another booking that overlaps');
            }

            if ($slot->remaining_capacity < $quantity) {
                abort(422, 'Not enough remaining capacity');
            }

            $booking = Booking::create([
                'user_id' => $user->id,
                'slot_id' => $slotId,
                'quantity' => $quantity,
                'status' => 'active'
            ]);

            $slot->decrement('remaining_capacity', $quantity);

            return $booking;
        });
    }

    public function cancel(int $bookingId, int $quantity = null)
    {
        return DB::transaction(function () use ($bookingId, $quantity) {

            $booking = Booking::lockForUpdate()->findOrFail($bookingId);

            if ($booking->user_id !== Auth::id()) {
                abort(403, 'Unauthorized.');
            }

            if ($booking->status !== 'active') {
                abort(422, 'Booking already cancelled.');
            }

            $slot = TimeSlot::lockForUpdate()->find($booking->slot_id);

            // FULL cancellation
            if (!$quantity || $quantity >= $booking->quantity) {

                $slot->increment('remaining_capacity', $booking->quantity);
                app(\App\Services\WaitlistService::class)->process($slot);
                $booking->update(['status' => 'cancelled']);

                return ['message' => 'Booking fully cancelled'];
            }

            // PARTIAL cancellation
            if ($quantity < 1 || $quantity > $booking->quantity) {
                abort(422, 'Invalid cancellation quantity.');
            }

            $booking->decrement('quantity', $quantity);
            $slot->increment('remaining_capacity', $quantity);

            return ['message' => 'Booking partially cancelled'];
        });
    }

    public function myBookings()
    {
        return [
            'upcoming' => Booking::with('slot.event')
                ->where('user_id', Auth::id())
                ->active()
                ->upcoming()
                ->get(),

            'past' => Booking::with('slot.event')
                ->where('user_id', Auth::id())
                ->active()
                ->past()
                ->get(),
        ];
    }
}
