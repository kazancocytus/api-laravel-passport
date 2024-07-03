<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
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

    public function AddPersonalData(Request $request) 
    {

        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'unauthorized'
            ]);
        }

        if ([$user->mobile, $user->address] != null) {
            return response()->json([
                'status' => false,
                'message' => 'Personal Data Already Available'
            ]);
        }

        $validator = Validator::make($request->all(), [
            'mobile' => 'numeric|required|min:9',
            'address' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()
            ], 422);
        }

        $user->mobile = $request->mobile;
        $user->address = $request->address;
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Add Personal Data Success'
        ]);

    }

    public function UpdatePersonalData(Request $request) {

        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'unauthorized'
            ]);
        }

        if ([$user->mobile, $user->address == null]) {
            return response()->json([
                'status' => false,
                'message' => 'There is nothing to update personal data, first add data personal'
            ]);
        }

        $validator = Validator::make($request->all(), [
            'mobile' => 'required|min:9|numeric',
            'address' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()
            ], 422);
        }

        $user->mobile = $request->mobile;
        $user->address = $request->address;
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Updated Personal Data Success'
        ]);

    }

    public function changePassword(Request $request) 
    {

        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'old_password' => 'required',
            'new_password' => 'required|min:4|confirmed',
        ]);


        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()
            ]);
        }

        if (!Hash::check($request->old_password, $user->password) ) {            
            return response()->json([
                'status' => false,
                'message' => 'Old password is wrong'
            ]);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();
    
        return response()->json([
            'status' => true,
            'message' => 'Change Password Success'
        ]);


    }
}
