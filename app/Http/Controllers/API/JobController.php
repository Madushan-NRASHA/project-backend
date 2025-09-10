<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Job;
use Illuminate\Http\Request;

class JobController extends Controller
{
    // Get all jobs
    public function index()
    {
        return response()->json(Job::with('user')->get(), 200);
    }

    // Create a new job
   public function store(Request $request)
{
    $request->validate([
        'job_name'     => 'required|string',
        'user_id'      => 'required|exists:users,id',
        'description'  => 'required|string',
        'job_catogary' => 'required|string',
        'location'     => 'required|string',
        'salary_range' => 'nullable|string', // Optional
        'job_type'     => 'required|string', // Example: Full-time, Part-time, Remote
    ]);

    $job = Job::create($request->all());

    return response()->json([
        'message' => 'Job created successfully',
        'data'    => $job
    ], 201);
}

    // Show a specific job
    public function show($id)
    {
        $job = Job::with('user')->find($id);

        if (!$job) {
            return response()->json(['message' => 'Job not found'], 404);
        }

        return response()->json($job);
    }

    // Update a job
    public function update(Request $request, $id)
    {
        $job = Job::find($id);

        if (!$job) {
            return response()->json(['message' => 'Job not found'], 404);
        }

        $job->update($request->only(['job_name', 'user_id', 'Description']));

        return response()->json([
            'message' => 'Job updated successfully',
            'data' => $job
        ]);
    }

    // Delete a job
    public function destroy($id)
    {
        $job = Job::find($id);

        if (!$job) {
            return response()->json(['message' => 'Job not found'], 404);
        }

        $job->delete();

        return response()->json(['message' => 'Job deleted successfully']);
    }
}
