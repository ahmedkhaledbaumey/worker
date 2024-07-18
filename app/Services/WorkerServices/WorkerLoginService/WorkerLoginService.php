<?php

namespace App\Services\WorkerServices\WorkerLoginService;

use App\Models\Worker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Auth\AuthenticationException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class WorkerLoginService
{
    protected $model;

    public function __construct()
    {
        $this->model = new Worker;
    }

    public function validation($request)
    {
        $validator = Validator::make($request->all(), $request->rules());

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        return $validator;
    }

    public function isValidData($data)
    {
        try {
            if (!$token = auth('worker')->attempt($data->validated())) {
                return response()->json(['error' => 'Invalid data'], 401);
            }
            return $token;
            // return $this->createNewToken($token);
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

    public function getStatus($email)
    {
        $worker = $this->model->whereEmail($email)->first();
        if (!$worker) {
            return response()->json(['error' => 'Worker not found'], 404);
        }
        $status = $worker->status;



        return $status;
    }
    public function isverified($email)
    {
        $worker = $this->model->whereEmail($email)->first();
        if (!$worker) {
            return response()->json(['error' => 'Worker not found'], 404);
        }

        // Check if the worker is verified
        return $worker->verified_at;

        // return $worker->verified_at !== null;
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

    // public function login($request)
    // {
    //     $data = $this->validation($request);

    //     $token = $this->isValidData($data);

    //     $status = $this->getStatus($request->email);

    //     if ($status == 0) {
    //         return response()->json(["error" => "Account is pending. Please wait for approval."], 422);
    //     }

    //     return  $this->createNewToken($token);
    // }


    public function login($request)
    {
        try {
            DB::beginTransaction();


            // Validate data
            $data = $this->validation($request);

            // Attempt login and get token
            $token = $this->isValidData($data);

            // Check user status
            $status = $this->getStatus($request->email);
            if ($this->isverified($request->email) == null) {
                DB::commit(); //مش عارف لسه  
                return response()->json(["error" => "Account is not verified."], 422);
            } elseif ($status == 0) {
                DB::commit(); //مش عارف لسه 
                return response()->json(["error" => "Account is pending. Please wait for approval."], 422);
            }

            // Create a new token
            $response = $this->createNewToken($token);
            DB::commit();
            return $response;
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'An error occurred.'], 500);
        }
    }
}
