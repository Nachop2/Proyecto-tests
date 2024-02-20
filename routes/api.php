<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TestController;
use App\Http\Controllers\CategoryController;
use App\Models\Test;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/upload-test', [TestController::class, 'store']);
});

Route::middleware('auth:sanctum')->get('/download-test/{id}', function ($id) {
    // Retrieve the test record from the database
    $user = Auth::user();
    $test = Test::findOrFail($id);

    switch ($test->visibility) {
        case 'private':
            if ($test->user_id !== $user->id) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
            break;

        case 'friends':
            $isFriend = $user->friends()->where('id', $test->user_id)->exists() ||
                $user->friendOf()->where('id', $test->user_id)->exists();
            if (!$isFriend) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
            break;
    }

    // Retrieve the file path from the test record
    $filePath = $test->test_src;

    // Read the JSON content from the file
    $fileContents = Storage::disk('public')->get($filePath);

    // Return the file content as a response with appropriate headers
    return response($fileContents, 200)
        ->header('Content-Type', 'application/json')
        ->header('Content-Disposition', 'attachment; filename="' . basename($filePath) . '"');
});

Route::middleware('auth:sanctum')->get('/user/tests', [TestController::class, 'getUserTests']);
Route::middleware('auth:sanctum')->get('/friend-tests', [TestController::class, 'getFriendTests']);
Route::get('/public-tests', [TestController::class, 'getPublicTests']);
Route::get('/play/{id}', [TestController::class, 'playTest']);

Route::middleware('auth:sanctum')->get('/user/test/{id}', [TestController::class, 'getTest']);
Route::middleware('auth:sanctum')->put('/user/test/{id}', [TestController::class, 'update']);

Route::get('/categories', [CategoryController::class, 'index']);