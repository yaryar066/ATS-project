<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JobOpening;

class JobController extends Controller
{
    public function index(Request $request)
    {
        return JobOpening::with('user')->latest()->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'location' => 'required|string',
            'salary' => 'nullable|string',
        ]);

        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized user.'], 401);
        }

        $job = JobOpening::create([
            'user_id' => $user->id,
            'title' => $request->title,
            'description' => $request->description,
            'location' => $request->location,
            'salary' => $request->salary,
        ]);

        return response()->json([
            'message' => 'Job opening created successfully!',
            'job' => $job
        ], 201);
    }

    public function destroy($id, Request $request)
    {
        $job = JobOpening::find($id);
        if (!$job) {
            return response()->json(['message' => 'Job not found.'], 404);
        }

        $job->delete();

        return response()->json([
            'message' => 'Job deleted successfully!'
        ], 200);
    }
}