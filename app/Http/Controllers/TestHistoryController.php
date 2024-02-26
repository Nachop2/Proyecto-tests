<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\TestHistory;
class TestHistoryController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $testHistories = $user->testHistories()
            ->with(['test' => function ($query) {
                $query->with('user', 'categories');
            }])
            ->get()
            ->map(function ($testHistory) {
                return [
                    'name' => $testHistory->test->name,
                    'author' => $testHistory->test->user->name, // Adjust based on your author relationship
                    'id' => $testHistory->test->id,
                    'score' => $testHistory->max_score,
                    'category_names' => $testHistory->test->categories->pluck('name'),
                    'played_at' => $testHistory->played_at,
                ];
            });

        return response()->json($testHistories);
    }

    public function updateMaxScore(Request $request, $testHistoryId)
    {
        $request->validate([
            'grade' => 'required|numeric',
        ]);

        $testHistory = TestHistory::findOrFail($testHistoryId);

        if ($request->grade > $testHistory->max_score) {
            $testHistory->update(['max_score' => $request->grade]);
            $testHistory->save();
            return response()->json(['message' => 'Max score updated successfully']);
        }

        return response()->json(['message' => 'Max score not updated. New score is not higher.'], 422);
    }
}
