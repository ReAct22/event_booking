<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Event;
use App\Models\TimeSlot;
use App\Models\Booking;
use App\Models\Waitlist;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

// use DB;

class ComplexEventSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            // =============================
            // 1️⃣ CREATE 300 USERS
            // =============================
            User::factory(300)->create();

            $users = User::all();

            // =============================
            // 2️⃣ CREATE 100 EVENTS
            // =============================
            $events = Event::factory(100)->create([
                'created_by' => $users->random()->id
            ]);

            // =============================
            // 3️⃣ CREATE SLOTS
            // 30 past, 70 upcoming
            // =============================
            foreach ($events as $index => $event) {

                $isPast = $index < 30;

                $start = $isPast
                    ? Carbon::now()->subDays(rand(10, 60))
                    : Carbon::now()->addDays(rand(1, 60));

                // Multi slot event (3 slot per event)
                for ($i = 0; $i < 3; $i++) {

                    TimeSlot::create([
                        'event_id' => $event->id,
                        'start_time' => $start->copy()->addDays($i),
                        'end_time' => $start->copy()->addDays($i)->addHours(2),
                        'capacity' => 20,
                        'remaining_capacity' => 20
                    ]);
                }
            }

            $slots = TimeSlot::all();

            // =============================
            // 4️⃣ FULL SLOT + WAITLIST
            // =============================
            $fullSlot = $slots->random();

            // Fill slot to max capacity
            for ($i = 0; $i < 20; $i++) {
                Booking::create([
                    'user_id' => $users->random()->id,
                    'slot_id' => $fullSlot->id,
                    'quantity' => 1,
                    'status' => 'active'
                ]);
            }

            $fullSlot->update(['remaining_capacity' => 0]);

            // Add waitlist
            for ($i = 1; $i <= 5; $i++) {
                Waitlist::create([
                    'user_id' => $users->random()->id,
                    'slot_id' => $fullSlot->id,
                    'quantity' => 1,
                    'position' => $i,
                    'status' => 'waiting'
                ]);
            }

            // =============================
            // 5️⃣ BUSY USER (multiple booking)
            // =============================
            $busyUser = $users->first();

            $randomSlots = $slots->random(10);

            foreach ($randomSlots as $slot) {
                Booking::create([
                    'user_id' => $busyUser->id,
                    'slot_id' => $slot->id,
                    'quantity' => 1,
                    'status' => 'active'
                ]);
            }

            // =============================
            // 6️⃣ CONFLICT OVERLAP
            // =============================
            $conflictSlot1 = $slots->random();
            $conflictSlot2 = TimeSlot::create([
                'event_id' => $conflictSlot1->event_id,
                'start_time' => $conflictSlot1->start_time,
                'end_time' => $conflictSlot1->end_time,
                'capacity' => 10,
                'remaining_capacity' => 10
            ]);

            Booking::create([
                'user_id' => $busyUser->id,
                'slot_id' => $conflictSlot1->id,
                'quantity' => 1,
                'status' => 'active'
            ]);

            Booking::create([
                'user_id' => $busyUser->id,
                'slot_id' => $conflictSlot2->id,
                'quantity' => 1,
                'status' => 'active'
            ]);

            // =============================
            // 7️⃣ CANCELLATION CHAIN
            // =============================
            $firstBooking = Booking::where('slot_id', $fullSlot->id)->first();
            $firstBooking->update(['status' => 'cancelled']);

            $nextWait = Waitlist::where('slot_id', $fullSlot->id)
                ->orderBy('position')
                ->first();

            if ($nextWait) {
                Booking::create([
                    'user_id' => $nextWait->user_id,
                    'slot_id' => $fullSlot->id,
                    'quantity' => 1,
                    'status' => 'active'
                ]);

                $nextWait->update(['status' => 'joined']);
            }
        });
    }
}
