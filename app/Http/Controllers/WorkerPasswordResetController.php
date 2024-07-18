<?php

namespace App\Http\Controllers;

use Exception;
use Validator;
use App\Models\Client;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\File;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Models\Worker;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use App\Mail\WorkerPasswordResetEmail;


class WorkerPasswordResetController extends Controller
{

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $worker = Worker::where('email', $request->email)->first();

        if (!$worker) {
            return response()->json(['error' => 'Worker not found'], 404);
        }

        $token = Str::random(60);
        $expiry = now()->addMinutes(60); // Set token expiry time

        // Update worker with the reset token and expiry using save()
        $worker->password_reset_token = $token;
        $worker->password_reset_token_expiry = $expiry;
        $worker->save();

        // Send password reset email
        Mail::to($worker->email)->send(new WorkerPasswordResetEmail($worker));

        return response()->json([
            'message' => 'Password reset email sent',
            'worker' => $worker
        ]);
    }


    // public function sendResetLinkEmail(Request $request)
    // {
    //     $request->validate(['email' => 'required|email']);

    //     $worker = Worker::where('email', $request->email)->first();

    //     if (!$worker) {
    //         return response()->json(['error' => 'Worker not found'], 404);
    //     }

    //     $token = Str::random(60);
    //     $expiry = now()->addMinutes(60); // Set token expiry time

    //     // Update worker with the reset token and expiry
    //     $worker->update([
    //         'password_reset_token' => $token,
    //         'password_reset_token_expiry' => $expiry,
    //     ]);


    //     // Send password reset email
    //     Mail::to($worker->email)->send(new WorkerPasswordResetEmail($worker));

    //     return response()->json([
    //         'message' => 'Password reset email sent',
    //         'worker' => $worker
    //     ]);
    // }

    public function resetPassword(Request $request, $token)
    {
        // Validate request data
        $request->validate([
            'password' => 'required|min:8|confirmed',
        ]);

        // Find the worker by the reset token
        $worker = Worker::where('password_reset_token', $token)
            ->where('password_reset_token_expiry', '>', now())
            ->first();

        if (!$worker) {
            return response()->json(['error' => 'Invalid or expired token'], 401);
        }
        $worker->password = bcrypt($request->password);
        $worker->password_reset_token = null;
        $worker->password_reset_token_expiry = null;
        $worker->save();
        // Update worker's password and reset token fields
        // $worker->update([
        //     'password' => bcrypt($request->password),
        //     'password_reset_token' => null,
        //     'password_reset_token_expiry' => null,
        // ]);

        return response()->json(['message' => 'Password reset successfully']);
    }
}
