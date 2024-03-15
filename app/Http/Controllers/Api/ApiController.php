<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\Passport;

class ApiController extends Controller
{
    public function register(Request $request)
    { 
        Passport::ignoreRoutes();
        $request->validate([
            "name" => "required",
            "email" => "required|email|unique:users",
            "password" => "required|confirmed",
        ]);

        User::create([
            "name" => $request->name,
            "email" => $request->email,
            "password" => Hash::make($request->password),
        ]);

        return response()->json([
            "status" => true,
            "message" => "User Created Successfully",
        ]);
    }

    public function login(Request $request)
    {
        Passport::ignoreRoutes();
        $request->validate([
            "email" => "required|email",
            "password" => "required",
        ]);

        if(Auth::attempt([
            "email" => $request->email,
            "password" => $request->password,
        ])){
            // User Exist
            $user = Auth::user();

            $token = $user->createToken("myToken")->accessToken;

            return response()->json([
                "status" => true,
                "message" => "User Login Successfully",
                "token" => $token,
            ]);

        } else {
            return response()->json([
                "status" => false,
                "message" => "Invalid Login"
            ]);
        }
    }

    public function profile()
    {
        $user = Auth::user();

        return response()->json([
            "status" => true,
            "message" => "Profile Information",
            "data" => $user,
        ]);
    }

    public function logout()
    {
        auth()->user()->token()->revoke();

        return response()->json([
            "status" => true,
            "message" => "User Logouted",
        ]);
    }
}
