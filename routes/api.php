<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TestController;
use App\Models\Test;
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
    $test = Test::findOrFail($id);

    // Retrieve the file using the stored file path
    $filePath = $test->test_src;
    $fileContents = Storage::get($filePath);

    // Return the file as a response
    return response($fileContents, 200)->header('Content-Type', 'application/json');
});

Route::middleware('auth:sanctum')->get('/user/tests', [TestController::class, 'getUserTests']);