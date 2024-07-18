<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WorkerReviewController;
use App\Http\Controllers\WorkerPasswordResetController;
use App\Http\Controllers\AdminDashboard\PostStatusController;
use App\Http\Controllers\AdminDashboard\AdminNotificationController;
use App\Http\Controllers\{AdminController, ClientController, ClientOrderController, PostController, WorkerController};

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// middleware('DbBackup')->     
Route::prefix('auth')->group(function () {
    Route::controller(AdminController::class)->prefix('admin')->group(function () {

        Route::post('/login', 'login');
        Route::post('/register', 'register');
        Route::post('/logout', 'logout');
        Route::post('/refresh', 'refresh');
        Route::get('/user-profile', 'userProfile');
    });

    Route::controller(WorkerController::class)->prefix('worker')->group(function () {
        Route::post('/login', 'login');
        Route::post('/register', 'register');
        Route::post('/logout', 'logout');
        Route::post('/refresh', 'refresh');
        Route::get('/user-profile', 'userProfile');
        Route::get('/verify/{token}', 'verify');
    });

    Route::controller(ClientController::class)->prefix('client')->group(function ($router) {
        Route::post('/login', 'login');
        Route::post('/register', 'register');
        Route::post('/logout', 'logout');
        Route::post('/refresh', 'refresh');
        Route::get('/user-profile', 'userProfile');
    });

    Route::controller(WorkerPasswordResetController::class)->prefix('worker')->group(function ($router) {

        Route::post('/send-password-reset-link', 'sendResetLinkEmail');
        Route::post('/reset-password/{token}', 'resetPassword');
    });
});
// routes/api.php



Route::get('/Unauthorized', function () {
    return response()->json([
        "message" => "Unauthorized"
    ],  401);
})->name('login');

Route::controller(AdminNotificationController::class)
    ->middleware("auth:admin")
    ->prefix('admin/notifications')->group(function () {
        Route::get('/all', 'index');
        Route::get('/unread', 'unread');
        Route::get('/markRead', 'markRead');
        Route::get('/markReadOne/{id}', 'markReadOne');
        Route::delete('/delete', 'delete');
        Route::delete('/deleteOne/{id}', 'deleteOne');
    });



Route::controller(PostController::class)->prefix('worker/post')->group(function () {
    Route::post('/add', 'store')->middleware("auth:worker");
    Route::get('/all', 'allPost')->middleware("auth:admin");
    Route::get('/onePost/{post_id}', 'onePost')->middleware("auth:admin");
    Route::get('/approved', 'showApproved');
    // middleware('DbBackup')->
});
Route::controller(PostStatusController::class)->prefix('admin/post')->group(function () {
    Route::post('/change', 'changeStatus')->middleware("auth:admin");
    // middleware('DbBackup')->
});
Route::controller(ClientOrderController::class)->prefix('client')->group(function () {
    Route::post('/makeOrder', 'makeOrder')->middleware("auth:admin");;
    // middleware('DbBackup')->
});
Route::controller(ClientOrderController::class)->prefix('worker')->group(function () {
    Route::get('/pending/orders', 'workerOrder')->middleware("auth:worker");
    Route::post('/update/orders/{id}', 'update')->middleware("auth:worker");
    // middleware('DbBackup')->
});
Route::controller(WorkerReviewController::class)->prefix('worker')->group(function () {
    Route::post('/review', 'store')->middleware("auth:client");
    Route::get('/review/post/{post_d}', 'postRate')->middleware("auth:worker");
    // middleware('DbBackup')->
});









// use App\Http\Controllers\{AdminController, ClientController, WorkerController};
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Route;

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

// Route::middleware('DbBackup')->prefix('auth')->group(function () {
//     $controllers = ['login', 'register', 'logout', 'refresh', 'userProfile'];

//     Route::prefix('admin')->group(function () use ($controllers, $adminController) {
//         Route::controller(AdminController::class, $controllers);
//     });

//     Route::prefix('worker')->group(function () use ($controllers) {
//         Route::controller(WorkerController::class, $controllers);
//     });

//     Route::prefix('client')->group(function () use ($controllers) {
//         Route::controller(ClientController::class, $controllers);
//     });
// });



// use App\Http\Controllers\{AdminController, ClientController, WorkerController};
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Route;

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

// Route::middleware('DbBackup')->prefix('auth')->group(function () {
//     Route::prefix('admin')->group(function () {
//         Route::controller(AdminController::class, [
//             'post:login',
//             'post:register',
//             'post:logout',
//             'post:refresh',
//             'get:user-profile',
//         ]);
//     });

//     Route::prefix('worker')->group(function () {
//         Route::controller(WorkerController::class, [
//             'post:login',
//             'post:register',
//             'post:logout',
//             'post:refresh',
//             'get:user-profile',
//         ]);
//     });

//     Route::prefix('client')->group(function () {
//         Route::controller(ClientController::class, [
//             'post:login',
//             'post:register',
//             'post:logout',
//             'post:refresh',
//             'get:user-profile',
//         ]);
//     });
// });
