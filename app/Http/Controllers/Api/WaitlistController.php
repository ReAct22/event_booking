<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Services\WaitlistService;
use App\Http\Controllers\Controller;

class WaitlistController extends Controller
{
    protected $service;

    public function __construct(WaitlistService $service)
    {
        $this->service = $service;
    }

    /*
    |--------------------------------------------------------------------------
    | POST /waitlist
    |--------------------------------------------------------------------------
    */
    public function join(Request $request)
    {
        $validated = $request->validate([
            'slot_id' => 'required|exists:time_slots,id',
            'quantity' => 'required|integer|min:1|max:5'
        ]);

        return response()->json(
            $this->service->join(
                $validated['slot_id'],
                $validated['quantity']
            ),
            201
        );
    }

    /*
    |--------------------------------------------------------------------------
    | POST /waitlist/cancel/{id}
    |--------------------------------------------------------------------------
    */
    public function cancel($id)
    {
        return response()->json(
            $this->service->cancel($id)
        );
    }
}
