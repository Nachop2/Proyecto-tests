<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Models\Test;
use Illuminate\Support\Facades\Storage;

class TestController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'visibility' => 'required|string',
            'test_file' => 'required|file',
            'category_ids' => 'required|array',
        ]);

        $user = Auth::user();
        $fileName = $request->file('test_file')->getClientOriginalName();
        $path = $request->file('test_file')->store('tests', 'public'); // Stores in storage/app/public/tests

        $test = new Test();
        $test->name = $request->name;
        $test->visibility = $request->visibility;
        $test->test_src = $path; // Save the path
        $test->user_id = $user->id;
        $test->save();
        $test->categories()->attach($request['category_ids']);


        return response()->json(['message' => 'Test uploaded successfully', 'test' => $test]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string',
            'visibility' => 'required|string',
            'test_file' => 'nullable|file',
            'category_ids' => 'required|array',
        ]);

        $test = Test::findOrFail($id);

        // Check if user owns the test
        if ($test->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // If a new test file is uploaded
        if ($request->hasFile('test_file')) {
            // Delete the old file if it exists
            if ($test->test_src && Storage::disk('public')->exists($test->test_src)) {
                Storage::disk('public')->delete($test->test_src);
            }

            // Store the new file and update the path
            $fileName = $request->file('test_file')->getClientOriginalName();
            $path = $request->file('test_file')->store('tests', 'public');
            $test->test_src = $path; // Update the path
        }

        // Update other fields
        $test->name = $request->name;
        $test->visibility = $request->visibility;
        $test->save();

        // Update categories if provided
        if ($request->has('category_ids')) {
            $test->categories()->sync($request['category_ids']);
        }

        return response()->json(['message' => 'Test updated successfully', 'test' => $test]);
    }

    public function getUserTests()
    {
        $user = Auth::user();

        // Retrieve the test names associated with the user
        $tests = Test::where('user_id', $user->id)->with('categories')->get(['id', 'name'])->map(function ($test) {
            return [
                'id' => $test->id,
                'name' => $test->name,
                'category_names' => $test->categories->pluck('name')
            ];
        });
        // Return the test names as JSON response
        return response()->json(['tests' => $tests], 200);
    }

    public function getFriendTests(Request $request)
    {
        $user = Auth::user();

        // Retrieve all tests where visibility is 'friend'
        $tests = Test::where('visibility', 'friend')->get()->filter(function ($test) use ($user) {
            // Check if the authenticated user is a friend of the test owner
            return $user->friends()->where('id', $test->user_id)->exists() ||
                $user->friendOf()->where('id', $test->user_id)->exists();
        });

        return response()->json($tests);
    }
}
