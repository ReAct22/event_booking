<?php

namespace App\Services;

use App\Models\Event;
use Illuminate\Support\Facades\Auth;

class EventService
{
    // Get All Event
    public function getAll($search = null, $perPage = 10)
    {
        $query = Event::with(['creator', 'timeSlots']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            });
        }

        return $query
            ->latest()
            ->paginate($perPage);
    }

    // Get Single Event
    public function getById($id)
    {
        return Event::with(['creator', 'timeSlots'])->findOrFail($id);
    }

    // create event
    public function create(array $data)
    {
        return Event::create([
            'title' => $data['title'],
            'description' => $data['description'],
            'location' => $data['location'],
            'created_by' => Auth::id(),
        ]);
    }

    // update event
    public function update(Event $event, array $data)
    {
        $this->authorizeOwner($event);

        $event->update([
            'title' => $data['title'],
            'description' => $data['description'],
            'location' => $data['location']
        ]);

        return $event;
    }

    public function delete(Event $event)
    {
        $this->authorizeOwner($event);

        $event->delete();
    }

    private function authorizeOwner(Event $event)
    {
        if ($event->created_by !== Auth::id()) {
            abort(403, 'You are not allowed to modify this event');
        }
    }
}
