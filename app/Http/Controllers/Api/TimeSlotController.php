<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\TimeSlot;
use App\Services\TimeSlotService;
use Illuminate\Auth\Events\Validated;
use Illuminate\Http\Request;

class TimeSlotController extends Controller
{
    protected $service;

    public function __construct(TimeSlotService $service)
    {
        $this->service = $service;
    }

    public function store(Request $request, Event $event){
        $validated = $request->validate([
            'start_time' => 'required|date',
            'end_time' => 'required|date',
            'capacity' => 'required|integer|min:1'
        ]);

        return response()->json(
            $this->service->create($event, $validated),
            201
        );
    }

    public function update(Request $request, TimeSlot $slot){
        $validated = $request->validate([
            'start_time' => 'required|date',
            'end_time' => 'required|date',
            'capacity' => 'required|integer|min:1'
        ]);

        return response()->json(
            $this->service->update($slot, $validated)
        );
    }

    public function destroy(TimeSlot $slot){
        $this->service->delete($slot);
        return response()->json([
            'message' => 'Slot deleted successfully'
        ]);
    }
}
