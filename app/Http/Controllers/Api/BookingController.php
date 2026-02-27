<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\BookingService;

class BookingController extends Controller
{
    protected $service;

    public function __construct(BookingService $service)
    {
        $this->service = $service;
    }

    /*
    |--------------------------------------------------------------------------
    | POST /book
    |--------------------------------------------------------------------------
    */
    public function book(Request $request)
    {
        $validated = $request->validate([
            'slot_id' => 'required|exists:time_slots,id',
            'quantity' => 'required|integer|min:1|max:5'
        ]);

        return response()->json(
            $this->service->book($validated['slot_id'], $validated['quantity']),
            201
        );
    }

    /*
    |--------------------------------------------------------------------------
    | POST /cancel/{booking}
    |--------------------------------------------------------------------------
    */
    public function cancel(Request $request, $bookingId)
    {
        $validated = $request->validate([
            'quantity' => 'nullable|integer|min:1'
        ]);

        return response()->json(
            $this->service->cancel($bookingId, $validated['quantity'] ?? null)
        );
    }

    /*
    |--------------------------------------------------------------------------
    | GET /my-bookings
    |--------------------------------------------------------------------------
    */
    public function myBookings()
    {
        return response()->json(
            $this->service->myBookings()
        );
    }
}
