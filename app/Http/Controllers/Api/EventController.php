<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Services\EventService;
use Illuminate\Http\Request;

class EventController extends Controller
{

    protected $eventService;

    public function __construct(EventService $eventService)
    {
        $this->eventService = $eventService;
    }
    // Get / Events
    public function index(Request $request){
        $search = $request->query('search');
        $perPage = $request->query('per_page', 10);

        return response()->json(
            $this->eventService->getAll($search, $perPage)
        );
    }

    public function show($id){
        return response()->json(
            $this->eventService->getById($id)
        );
    }

    public function store(Request $request){
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'location' => 'required|string|max:255'
        ]);

        $event = $this->eventService->create($validated);

        return response()->json($event, 201);
    }

    public function update(Request $request, Event $event){
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'location' => 'required|string|max:255'
        ]);

        return response()->json(
            $this->eventService->update($event, $validated)
        );
    }

    public function destroy(Event $event){
        $this->eventService->delete($event);

        return response()->json([
            'message' => 'Event deleted successfully'
        ]);
    }
}
