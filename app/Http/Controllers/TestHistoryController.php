<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TestHistoryController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $testHistories = $user->testHistories()->with('test')->get();

        return response()->json($testHistories);
    }
}
