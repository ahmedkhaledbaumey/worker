<?php

namespace App\Http\Controllers;

use Exception;
use Validator;
use App\Models\Worker;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginWorkerRequest;
use App\Http\Requests\registerWorkerRequest;
use App\Services\WorkerServices\WorkerLoginService\WorkerLoginService;
use App\Services\WorkerServices\WorkerRegisterService\WorkerRegisterService;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\File;
use Tymon\JWTAuth\Contracts\Providers\Storage;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class WorkerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:worker', ['except' => ['login', 'register', 'verify']]);
    }

    public function login(LoginWorkerRequest $request)
    {
        // try {
        //     $validator = Validator::make($request->all(), [
        //         'email' => 'required|email',
        //         'password' => 'required|string|min:6',
        //     ]);

        //     if ($validator->fails()) {
        //         return response()->json(['error' => $validator->errors()], 422);
        //     }

        //     if (!$token = auth('worker')->attempt($validator->validated())) {
        //         return response()->json(['error' => 'Unauthorized'], 401);
        //     }

        //     return $this->createNewToken($token);
        // } catch (TokenExpiredException $e) {
        //     return response()->json(['error' => 'Token has expired'], 401);
        // } catch (TokenInvalidException $e) {
        //     return response()->json(['error' => 'Invalid token'], 401);
        // } catch (JWTException $e) {
        //     return response()->json(['error' => 'Error while processing the token'], 500);
        // } catch (AuthenticationException $e) {
        //     return response()->json(['error' => 'Unauthenticated'], 401);
        // } catch (HttpException $e) {
        //     return response()->json(['error' => $e->getMessage()], $e->getStatusCode());
        // }
        return (new WorkerLoginService())->login($request);
    }





    public function register(registerWorkerRequest $request)
    {
        // try {
        //     $validator = Validator::make($request->all(), [
        //         'name' => 'required|string',
        //         'email' => 'required|email|unique:workers',
        //         'password' => 'required|string|min:6',
        //         'phone' => 'required|string|max:17',
        //         'photo' => 'nullable|image|mimes:png,jpg,jpeg,pdf',
        //         'location' => 'required|string',
        //     ]);

        //     if ($validator->fails()) {
        //         return response()->json(['error' => $validator->errors()->first()], 422);
        //     }

        // $inputData = $validator->validated();
        // $inputData['password'] = bcrypt($request->password);

        // if ($request->hasFile('photo')) {
        //     $photo = $request->file('photo');

        //     // Check if the file already exists
        //     $existingPhotoPath = 'public/workers/' . $photo->getClientOriginalName();
        //     if (File::exists($existingPhotoPath)) {
        //         return response()->json(['error' => 'The photo already exists'], 422);
        //     }

        //     $inputData['photo'] = $photo->store('workers', 'public');
        // }

        // $worker = Worker::create($inputData);

        //     // Optionally, you may automatically log in the registered user.
        //     $token = auth('worker')->login($worker);

        //     return response()->json([
        //         'message' => 'Worker registered successfully',
        //         'user' => $worker,
        //         'token' => $token,
        //         'token_type' => 'Bearer',

        //     ]);
        // } catch (Exception $e) {
        //     return response()->json(['error' => 'Registration failed. ' . $e->getMessage()], 500);
        // }

        return (new WorkerRegisterService)->register($request);
    }



    public function verify($token)
    {
        try {
            // Find the worker by the verification token
            $worker = Worker::whereVerificationToken($token)->first();

            // Check if the worker with the given token exists
            if (!$worker) {
                return response()->json(['error' => 'Invalid token'], 401);
            }

            // Check if the worker is already verified
            if ($worker->verified_at) {
                return response()->json(['error' => 'Account already verified'], 401);
            }

            // Update worker details upon successful verification
            $worker->verification_token = null;
            $worker->verified_at = now();
            $worker->save();

            // Return a success message
            return response()->json(['message' => 'Your account has been verified'], 200);
        } catch (\Exception $e) {
            // Log the exception for debugging
            // Log::error('Error in verify function: ' . $e->getMessage());

            // Return an error response
            return response()->json(['error' => 'An error occurred during verification'], 500);
        }
    }


    // public function verify($token)
    // {
    //     $worker = Worker::whereVerificationToken($token)->first();
    //     if (!$worker) {
    //         return response()->json(['error' => 'Invalid  token'], 401);
    //     }
    //     $worker->verification_token = null;
    //     $worker->verified_at = now();
    //     $worker->save();

    //     return response()->json(['message' => 'your account has been verified'], 401);
    // }

    public function logout()
    {
        try {
            auth('worker')->logout();

            // Add cache control headers
            return response()->json(['message' => 'Worker successfully signed out'])->header('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        } catch (JWTException $e) {
            return response()->json(['error' => 'Error while logging out'], 500);
        }
    }

    public function refresh()
    {
        try {
            $newToken = auth('worker')->refresh();

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
            $worker = auth('worker')->user();

            if (!$worker) {
                return response()->json(['error' => 'Worker not authenticated'], 401);
            }

            return response()->json($worker);
        } catch (TokenInvalidException $e) {
            return response()->json(['error' => 'Invalid token'], 401);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Error fetching worker profile'], 500);
        }
    }

    protected function createNewToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('worker')->factory()->getTTL() * 60,
            'worker' => auth('worker')->user()
        ]);
    }
}
