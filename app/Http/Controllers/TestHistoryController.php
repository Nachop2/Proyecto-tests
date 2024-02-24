<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
                    'category_names' => $testHistory->test->categories->pluck('name'),
                    'played_at' => $testHistory->played_at,
                ];
            });

        return response()->json($testHistories);
    }
}
