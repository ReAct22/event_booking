<?php

namespace App\Http\Controllers\Api;

use App\Services\NotificationService;
use App\Http\Controllers\Controller;

class NotificationController extends Controller
{
    protected $service;

    public function __construct(NotificationService $service)
    {
        $this->service = $service;
    }

    /*
    |--------------------------------------------------------------------------
    | GET /notifications
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        return response()->json(
            $this->service->myNotifications()
        );
    }

    /*
    |--------------------------------------------------------------------------
    | GET /notifications/unread-count
    |--------------------------------------------------------------------------
    */
    public function unreadCount()
    {
        return response()->json([
            'unread' => $this->service->unreadCount()
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | POST /notifications/read/{id}
    |--------------------------------------------------------------------------
    */
    public function markAsRead($id)
    {
        return response()->json(
            $this->service->markAsRead($id)
        );
    }

    /*
    |--------------------------------------------------------------------------
    | POST /notifications/read-all
    |--------------------------------------------------------------------------
    */
    public function markAllAsRead()
    {
        return response()->json(
            $this->service->markAllAsRead()
        );
    }
}
