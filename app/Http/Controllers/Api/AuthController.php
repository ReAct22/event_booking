<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function register(Request $request){
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'password' => 'required|max:8',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'timezone' => $request->timezone ?? 'UTC'
        ]);
    }

    public function login(Request $request){
        if(!Auth::attempt($request->only(['email', 'password'])))
            return response()->json(['error' => 'Invalid']);

        $token = $request->user()->createToken('auth')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'login berhasil',
            'token' => $token,
            'data' => $request->user()
        ]);
    }
}
