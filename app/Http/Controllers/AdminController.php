<?php

namespace App\Http\Controllers;

use Exception;
use Validator;
use App\Models\Admin;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Auth\AuthenticationException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin', ['except' => ['login', 'register']]);
    }

    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required|string|min:6',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            }

            if (!$token = auth('admin')->attempt($validator->validated())) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            return $this->createNewToken($token); 
            

        } catch (TokenExpiredException $e) {
            return response()->json(['error' => 'Token has expired'], 401);
        } catch (TokenInvalidException $e) {
            return response()->json(['error' => 'Invalid token'], 401);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Error while processing the token'], 500);
        } catch (AuthenticationException $e) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        } catch (HttpException $e) {
            return response()->json(['error' => $e->getMessage()], $e->getStatusCode());
        }
    }


    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
                'email' => 'required|email|unique:admins',
                'password' => 'required|string|min:6',
                'phone' => 'required|string|max:17',
                'photo' => 'nullable|image|mimes:png,jpg,jpeg,pdf',

            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 422);
            }

            $inputData = $validator->validated();
            $inputData['password'] = bcrypt($request->password);

            if ($request->hasFile('photo')) {
                $photo = $request->file('photo');

                // Check if the file already exists
                $existingPhotoPath = 'public/admins/' . $photo->getClientOriginalName();
                if (File::exists($existingPhotoPath)) {
                    return response()->json(['error' => 'The photo already exists'], 422);
                }

                $inputData['photo'] = $photo->store('admins', 'public');
            }

            $admin = Admin::create($inputData);

            // Optionally, you may automatically log in the registered user.
            $token = auth('admin')->login($admin);

            return response()->json([
                'message' => 'Worker registered successfully',
                'admin' => $admin,
                'token' => $token,
                'token_type' => 'Bearer',
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => 'Registration failed. ' . $e->getMessage()], 500);
        }
    }


    public function logout()
    {
        try {
            // Verify the user is authenticated
            $admin = auth('admin')->user();

            if (!$admin) {
                return response()->json(['error' => 'Admin not authenticated'], 401);
            }

            // Logout the user
            auth('admin')->logout();

            // Add cache control headers
            return response()->json(['message' => 'Admin successfully signed out'])->header('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        } catch (JWTException $e) {
            return response()->json(['error' => 'Error while logging out'], 500);
        }
    }


    public function refresh()
    {
        try {
            $newToken = auth('admin')->refresh();

            if (!$newToken) {
                return response()->json(['error' => 'Invalid refresh token'], 401);
            }

            return $this->createNewToken($newToken);
        } catch (TokenExpiredException $e) {
            return response()->json(['error' => 'Token has expired'], 401);
        } catch (TokenInvalidException $e) {
            return response()->json(['error' => 'Invalid token'], 401);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Error while refreshing the token'], 500);
        }
    }

    public function userProfile()
    {
        try {
            $admin = auth('admin')->user();

            if (!$admin) {
                return response()->json(['error' => 'Admin not authenticated'], 401);
            }

            return response()->json($admin);
        } catch (TokenInvalidException $e) {
            return response()->json(['error' => 'Invalid token'], 401);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Error fetching admin profile'], 500);
        }
    }

    protected function createNewToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('admin')->factory()->getTTL() * 60,
            'admin' => auth('admin')->user()
        ]);
    }
}
