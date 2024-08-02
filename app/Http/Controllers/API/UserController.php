<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use APp\Models\User;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function updateProfile(Request $request, $id)
    {

        $request->validate([
            'name' => 'required|string|min:3',
            'profile' => 'nullable|image|mimes:jpeg,jpg,png,gif,svg|max:5120',
        ]);

        $user = User::findOrFail($id);

        if ($request->hasFile('profile')) {
            $path = $request->file('profile')->store('profiles', 'public');
            $user->profile = $path;
        }

        $user->name = $request->name;
        $user->save();

        return response()->json(['message' => 'Profile updated successfully', 'user' => $user], 200);
    }

    public function changePassword(Request $request, $id)
    {
        $request->validate([
            'old_password' => 'required|string',
            'new_password' => 'required|string|min:6'
        ]);

        $user = User::findOrFail($id);

        if (!Hash::check($request->old_password, $user->password)) {
            return response()->json(['message' => 'Password does not match with our records'], 400);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json(['message' => 'Password changed successfully'], 200);
    }
}
