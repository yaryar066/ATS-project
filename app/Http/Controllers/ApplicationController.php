<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Application;
use Illuminate\Support\Facades\File;

class ApplicationController extends Controller
{
    /**
     * Display a listing of applications.
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            
            if (!$user) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            if ($user->role === 'hr') {
                $applications = Application::with(['user', 'jobOpening'])->latest()->get();
            } else {
                $applications = Application::with('jobOpening')->where('user_id', $user->id)->latest()->get();
            }

            return response()->json($applications, 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Server Error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created application in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'job_opening_id' => 'required|exists:job_openings,id',
                'name' => 'required|string|max:255',
                'phone' => 'required|string|max:20',
                'portfolio' => 'nullable|string|max:255',
                'cover_letter' => 'required|string',
                'resume' => 'required|file|mimes:pdf,doc,docx,png,jpg,jpeg|max:20480', 
            ]);

            $user = $request->user();
            if (!$user || $user->role !== 'candidate') {
                return response()->json(['message' => 'Unauthorized. Only candidates can apply.'], 403);
            }

            // Check if already applied
            $existing = Application::where('user_id', $user->id)
                                    ->where('job_opening_id', $request->job_opening_id)
                                    ->first();

            if ($existing) {
                return response()->json(['message' => 'You have already applied for this job.'], 400);
            }

            // Handle Resume File Upload directly using public_path to completely avoid Windows path flysystem bugs
            $resumePath = null;
            if ($request->hasFile('resume')) {
                $file = $request->file('resume');
                $filename = time() . '_' . preg_replace('/\s+/', '_', $file->getClientOriginalName());
                
                $destinationPath = public_path('storage/resumes');
                if (!File::exists($destinationPath)) {
                    File::makeDirectory($destinationPath, 0777, true, true);
                }
                
                $file->move($destinationPath, $filename);
                $resumePath = '/storage/resumes/' . $filename;
            }

            // Create Application record
            $application = Application::create([
                'user_id' => $user->id,
                'name' => $request->name,
                'job_opening_id' => $request->job_opening_id,
                'phone' => $request->phone,
                'portfolio' => $request->portfolio,
                'cover_letter' => $request->cover_letter,
                'resume' => $resumePath,
                'status' => 'pending'
            ]);

            return response()->json([
                'message' => 'Application submitted successfully with Resume!',
                'application' => $application
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to submit application',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update application status (For HR: shortlisted, rejected, pending).
     */
    public function updateStatus(Request $request, $id)
    {
        $user = $request->user();
        if (!$user || $user->role !== 'hr') {
            return response()->json(['message' => 'Unauthorized. Only HR can update status.'], 403);
        }

        $request->validate([
            'status' => 'required|in:pending,shortlisted,rejected'
        ]);

        $application = Application::findOrFail($id);
        $application->status = $request->status;
        $application->save();

        return response()->json([
            'message' => 'Application status updated successfully.',
            'application' => $application
        ]);
    }
}