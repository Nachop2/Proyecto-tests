<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Test;
class TestController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'test_file' => 'required|file',
        ]);

        $user = Auth::user(); 
        $fileName = $request->file('test_file')->getClientOriginalName();
        $path = $request->file('test_file')->store('tests', 'public'); // Stores in storage/app/public/tests

        $test = new Test();
        $test->name = $request->name;
        $test->test_src = $path; // Save the path
        $test->user_id = $user->id;
        $test->save();

        return response()->json(['message' => 'Test uploaded successfully', 'test' => $test]);
    }

    public function getUserTests()
    {
        $user = Auth::user();

        // Retrieve the test names associated with the user
        $tests = Test::where('user_id', $user->id)->pluck('name');

        // Return the test names as JSON response
        return response()->json(['tests' => $tests], 200);
    }
}
