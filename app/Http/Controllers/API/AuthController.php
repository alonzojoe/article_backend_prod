<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;


class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:5'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);

        return response()->json(['status' => 'success', 'message' => 'User Created Successfully', 'user' => $user], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required',
            'password' => 'required'
        ]);

        $input = $request->only('email', 'password');

        $token = Auth::attempt($input);

        if (!$token) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid Username / Password',
            ], 401);
        }

        $user = Auth::user();

        return response()->json([
            'status' => 'success',
            'message' => 'Login successfully',
            'user' => $user,
            'authorization' => [
                'token' => $token,
                'type' => 'bearer'
            ]
        ], 200);
    }

    public function logout()
    {
        Auth::logout();
        return response()->json(['status' => 'success', 'message' => 'Logout successfully.'], 200);
    }

    public function me()
    {
        $user = Auth::user();
        return response()->json(['status' => 'success', 'user' => $user], 200);
    }

    public function checkSession()
    {
        return response()->json(['status' => 'success', 'data' => 'h0-_J1I3HwkIFgoqGJ6UM7DWw3V63ok1rZg-H5mQeNo'], 200);
    }

    public function verify($type)
    {
        return response()->json(['status' => 'success', 'data' => 'HwkIFgoqGh0-1I3-H5mQeNHwkIF7DWw3V63ok1__rZgo' . $type], 200);
    }
}
