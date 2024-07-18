<?php

namespace App\Services\WorkerServices\WorkerRegisterService;

use Log;
use App\Models\Worker;
use App\Mail\VerificationEmail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class WorkerRegisterService
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
    public function store($request)
    {
        $validator = $this->validation($request);
        $inputData = $validator->validated();
        $inputData['password'] = bcrypt($inputData['password']);

        if ($request->hasFile('photo')) {
            $photo = $request->file('photo');

            // Check if the file already exists
            $existingPhotoPath = 'public/workers/' . $photo->getClientOriginalName();
            if (File::exists($existingPhotoPath)) {
                return response()->json(['error' => 'The photo already exists'], 422);
            }

            $inputData['photo'] = $photo->store('workers', 'public');
        } else {
            // No photo file attached, use default image
            $defaultImage = '1.png';
            $inputData['photo'] = 'workers/' . $defaultImage;
        }


        $worker = Worker::create($inputData);

        return $worker;
    }



    // public function store($request)
    // {
    //     $validator = $this->validation($request);
    //     $inputData = $validator->validated();
    //     $inputData['password'] = bcrypt($inputData['password']);

    //     if ($request->hasFile('photo')) {
    //         $photo = $request->file('photo');

    //         // Check if the file already exists
    //         $existingPhotoPath = 'public/workers/' . $photo->getClientOriginalName();
    //         if (File::exists($existingPhotoPath)) {
    //             return response()->json(['error' => 'The photo already exists'], 422);
    //         }

    //         $inputData['photo'] = $photo->store('workers', 'public');
    //     }

    //     $worker = Worker::create($inputData);

    //     return $worker;
    // }

    public function generateToken($worker)
    {
        $token = substr(md5(rand(0, 9)  . $worker->email . time()), 0, 32);
        $worker->verification_token = $token;
        $worker->save();

        return $worker;
    }

    // public function sendEmail()
    // {
    //     // Implement email sending logic if needed

    // }

    public function sendEmail($worker)
    {

        // قم بإرسال البريد الإلكتروني باستخدام Laravel Mail
        Mail::to($worker->email)->send(new VerificationEmail($worker));
    }



    public function register($request)
    {
        try {
            DB::beginTransaction();
            $worker = $this->store($request);
            $this->generateToken($worker);
            $this->sendEmail($worker);
            DB::commit();
            return response()->json(
                ['message' => 'Account has been created. Please check your email.', 'worker' => $worker],
                200,
            );
        } catch (\Exception $e) {
            DB::rollBack();
            // Log the exception for debugging
            // Log::error('Error in WorkerRegisterService: ' . $e->getMessage());

            // Return a more informative error message
            return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }
}
