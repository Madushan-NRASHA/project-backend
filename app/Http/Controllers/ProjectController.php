<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class ProjectController extends Controller
{
    /**
     * Display a listing of projects.
     */
    public function index()
    {
        // All projects with related user
        $projects = Project::with('user')->get();
        return response()->json($projects);
    }

    /**
     * Store a newly created project.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id'     => 'required|exists:users,id',
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'photo'       => 'nullable|string',
            'link'        => 'nullable|string|max:255',
          
           
        ]);

        $project = Project::create($validated);

        return response()->json([
            'message' => 'Project created successfully',
            'project' => $project
        ], 201);
    }

    /**
     * Display the specified project.
     */
    public function show($id)
    {
        $project = Project::with('user')->findOrFail($id);
        return response()->json($project);
    }

    /**
     * Update the specified project.
     */
    public function update(Request $request, $id)
    {
        $project = Project::findOrFail($id);

        $validated = $request->validate([
            'user_id'     => 'sometimes|exists:users,id',
            'name'        => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'photo'       => 'nullable|string',
            'link'        => 'nullable|string|max:255',
          
          
        ]);

        $project->update($validated);

        return response()->json([
            'message' => 'Project updated successfully',
            'project' => $project
        ]);
    }

    /**
     * Remove the specified project.
     */
    public function destroy($id)
    {
        $project = Project::findOrFail($id);
        $project->delete();

        return response()->json([
            'message' => 'Project deleted successfully'
        ]);
    }

    /**
     * Get projects by user ID (for Flutter app)
     */
    public function getProjectsByUserId(Request $request): JsonResponse
    {
        try {
            $userId = $request->query('user_id');
            
            // Validate user_id parameter
            if (!$userId) {
                return response()->json([
                    'error' => 'user_id parameter is required'
                ], 400);
            }

            // Get projects for the user
            $projects = Project::where('user_id', $userId)
                ->select([
                    'id',
                    'name',
                    'description',
                    'photo',
                    'link',
                   
                    'created_at',
                    'updated_at'
                ])
                ->orderBy('created_at', 'desc')
                ->get();

            // Add title field for Flutter compatibility
            $projects = $projects->map(function ($project) {
                $project->title = $project->name; // Map name to title
                return $project;
            });

            // Log for debugging
            Log::info("Projects fetched for user {$userId}: " . $projects->count() . " projects found");

            return response()->json($projects, 200);

        } catch (\Exception $e) {
            Log::error("Error fetching projects for user: " . $e->getMessage());
            
            return response()->json([
                'error' => 'Failed to fetch projects',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Alternative endpoint - Get projects by user ID (RESTful style)
     */
    public function getUserProjects($userId): JsonResponse
    {
        try {
            // Validate user ID
            if (!$userId || !is_numeric($userId)) {
                return response()->json([
                    'error' => 'Valid user ID is required'
                ], 400);
            }

            // Get projects for the user with user relationship
            $projects = Project::where('user_id', $userId)
                ->with('user:id,name,email')
                ->select([
                    'id',
                    'user_id',
                    'name',
                    'description',
                    'photo',
                    'link',
                  
                    
                    'created_at',
                    'updated_at'
                ])
                ->orderBy('created_at', 'desc')
                ->get();

            // Add title field for Flutter compatibility
            $projects = $projects->map(function ($project) {
                $project->title = $project->name;
                return $project;
            });

            Log::info("Projects fetched for user {$userId}: " . $projects->count() . " projects found");

            // Return with data wrapper (optional)
            return response()->json([
                'success' => true,
                'data' => $projects,
                'count' => $projects->count()
            ], 200);

        } catch (\Exception $e) {
            Log::error("Error fetching user projects: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch projects',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all projects for authenticated user
     */
    public function getMyProjects(Request $request): JsonResponse
    {
        try {
            $user = $request->user(); // Get authenticated user
            
            if (!$user) {
                return response()->json([
                    'error' => 'Authentication required'
                ], 401);
            }

            $projects = Project::where('user_id', $user->id)
                ->select([
                    'id',
                    'name',
                    'description', 
                    'photo',
                    'link',
                  
                    
                    'created_at',
                    'updated_at'
                ])
                ->orderBy('created_at', 'desc')
                ->get();

            // Add title field for Flutter compatibility
            $projects = $projects->map(function ($project) {
                $project->title = $project->name;
                return $project;
            });

            return response()->json($projects, 200);

        } catch (\Exception $e) {
            Log::error("Error fetching authenticated user projects: " . $e->getMessage());
            
            return response()->json([
                'error' => 'Failed to fetch projects',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}