<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Models\Test;
use Illuminate\Support\Facades\Storage;
use App\Models\TestHistory;

class TestController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'visibility' => 'required|string',
            'description' => 'nullable|string',
            'test_file' => 'required|file',
            'category_ids' => 'required|array',
        ]);

        $user = Auth::user();
        $fileName = $request->file('test_file')->getClientOriginalName();
        $path = $request->file('test_file')->store('tests', 'public'); // Stores in storage/app/public/tests

        $test = new Test();
        $test->name = $request->name;
        $test->visibility = $request->visibility;
        if ($request->description) {
            $test->description = $request->description;
        }
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
            'description' => 'nullable|string',
            'test_file' => 'required|file',
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
        if ($request->description) {
            $test->description = $request->description;
        }
        $test->save();

        $test->categories()->sync($request['category_ids']);

        return response()->json(['message' => 'Test updated successfully', 'test' => $test]);
    }

    public function getTest(Request $request, $id)
    {
        // Retrieve the test record from the database
        $user = Auth::user();
        $test = Test::findOrFail($id);

        if ($test->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Retrieve the file path from the test record
        $filePath = $test->test_src;

        // Read the JSON content from the file
        $fileContents = Storage::disk('public')->get($filePath);

        $questions = json_decode($fileContents, true);

        // Construct the response data
        $responseData = [
            "test_id" => $test->id,
            "name" => $test->name,
            "category_names" => $test->categories->pluck('name'),
            "visibility" => $test->visibility,
            "description" => $test->description,
            "questions" => $questions,
        ];

        // Return the response with appropriate headers
        return response()->json($responseData, 200)
            ->header('Content-Type', 'application/json')
            ->header('Content-Disposition', 'attachment; filename="' . basename($filePath) . '"');
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

    public function getPublicTests()
    {
        $user = Auth::user();

        // Use pagination instead of getting all results
        $tests = Test::where('visibility', 'public')
            ->with('categories')
            ->paginate(10, ['id', 'name', 'description']) // Paginate results, 10 per page
            ->through(function ($test) {
                return [
                    'id' => $test->id,
                    'name' => $test->name,
                    'description' => $test->description,
                    'category_names' => $test->categories->pluck('name')
                ];
            });

        // Return the paginated tests as JSON response
        return response()->json($tests, 200); // $tests already contains pagination info
    }

    public function playTest(Test $test)
    {
        // Retrieve the test record from the database
        $author = $test->user;
        // add private/friend protection

        if (Auth::check()) {
            // The user is logged in
            $userId = Auth::id();

            // Check and manage the test play history limit logic here for the logged-in user
            $playCount = TestHistory::where('user_id', $userId)->count();

            // Maximum number of test plays to keep
            $limit = 10;
            if ($playCount >= $limit) {
                // Find the oldest played test and delete it
                TestHistory::where('user_id', $userId)
                    ->oldest('played_at')
                    ->limit($playCount - $limit + 1) // In case of multiple plays over the limit
                    ->delete();
            }
            // Record the new test play for the logged-in user
            TestHistory::create([
                'user_id' => $userId,
                'test_id' => $test->id,
                'played_at' => now(),
            ]);
        }
        // Retrieve the file path from the test record
        $filePath = $test->test_src;

        // Read the JSON content from the file
        $fileContents = Storage::disk('public')->get($filePath);

        $questions = json_decode($fileContents, true);

        // Construct the response data
        $responseData = [
            "test_id" => $test->id,
            "name" => $test->name,
            "category_names" => $test->categories->pluck('name'),
            "visibility" => $test->visibility,
            "description" => $test->description,
            "created_at" => $test->created_at,
            "updated_at" => $test->updated_at,
            "questions" => $questions,
            "author" => $author->name
        ];

        // Return the response with appropriate headers
        return response()->json($responseData, 200)
            ->header('Content-Type', 'application/json')
            ->header('Content-Disposition', 'attachment; filename="' . basename($filePath) . '"');
    }
}
