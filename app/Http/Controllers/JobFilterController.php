<?php

namespace App\Http\Controllers;

use App\Models\Job;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;

class JobFilterController extends Controller
{
    /** Display jobs page */
    public function index(Request $request): View
    {
        $query = Job::query();

        // Apply filters
        $this->applyFilters($query, $request);

        // Get paginated results
        $jobs = $query->orderBy('created_at', 'desc')->paginate(15);

        // Get all unique job categories for dropdown
        $jobCategories = Job::select('job_category')->distinct()->pluck('job_category');

        // Get filter counts
        $filterCounts = $this->getFilterCounts();

        return view('jobs.index', compact('jobs', 'jobCategories', 'filterCounts'));
    }

    /** API endpoint for filtered jobs */
    public function apiIndex(Request $request): JsonResponse
    {
        try {
            $query = Job::query();

            $this->applyFilters($query, $request);

            if ($request->boolean('paginate', true)) {
                $jobs = $query->orderBy('created_at', 'desc')
                    ->paginate($request->input('per_page', 15));
            } else {
                $jobs = $query->orderBy('created_at', 'desc')->get();
            }

            return response()->json([
                'success' => true,
                'data' => $jobs,
                'filters_applied' => $this->getAppliedFilters($request)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving jobs',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /** Get jobs by specific category */
    public function getByCategory(Request $request, string $category): JsonResponse
    {
        try {
            $query = Job::where('job_category', $category);

            if ($request->filled('search')) {
                $this->applySearchFilter($query, $request->search);
            }
            if ($request->filled('user_id')) {
                $query->where('user_id', $request->user_id);
            }

            if ($request->filled('location')) {
                $query->where('location', $request->location);
            }
            if ($request->filled('salary_range')) {
                $query->where('salary_range', $request->salary_range);
            }
            if ($request->filled('job_type')) {
                $query->where('job_type', $request->job_type);
            }

            $jobs = $query->orderBy('created_at', 'desc')->get();

            return response()->json([
                'success' => true,
                'category' => $category,
                'count' => $jobs->count(),
                'data' => $jobs
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving jobs for category: ' . $category,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /** Get all unique job categories with counts */
    public function getCategories(): JsonResponse
    {
        try {
            $categories = Job::selectRaw('job_category, COUNT(*) as job_count')
                ->groupBy('job_category')
                ->orderBy('job_category')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $categories
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving categories',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /** Get filter statistics */
    public function getStats(): JsonResponse
    {
        try {
            $filterCounts = $this->getFilterCounts();
            return response()->json([
                'success' => true,
                'data' => $filterCounts
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving stats',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /** Filter jobs with advanced options */
    public function filter(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'job_category' => 'nullable|string',
                'categories' => 'nullable|array',
                'categories.*' => 'string',
                'search' => 'nullable|string|max:255',
                'user_id' => 'nullable|integer',
                'location' => 'nullable|string|max:255',
                'salary_range' => 'nullable|string|max:255',
                'job_type' => 'nullable|string|max:255',
                'date_from' => 'nullable|date',
                'date_to' => 'nullable|date',
                'sort_by' => 'nullable|in:created_at,updated_at,job_name',
                'sort_order' => 'nullable|in:asc,desc',
                'per_page' => 'nullable|integer|min:1|max:100'
            ]);

            $query = Job::query();
            $this->applyFilters($query, $request);

            $sortBy = $request->input('sort_by', 'created_at');
            $sortOrder = $request->input('sort_order', 'desc');

            $query->orderBy($sortBy, $sortOrder);

            $perPage = $request->input('per_page', 15);
            $jobs = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $jobs,
                'filters_applied' => $this->getAppliedFilters($request),
                'total_results' => $jobs->total()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error filtering jobs',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /** Apply filters */
    private function applyFilters(Builder $query, Request $request): void
    {
        if ($request->filled('job_category')) {
            $query->where('job_category', $request->job_category);
        }

        if ($request->filled('categories')) {
            $categories = is_array($request->categories) ? $request->categories : explode(',', $request->categories);
            $query->whereIn('job_category', $categories);
        }

        if ($request->filled('search')) {
            $this->applySearchFilter($query, $request->search);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('location')) {
            $query->where('location', $request->location);
        }

        if ($request->filled('salary_range')) {
            $query->where('salary_range', $request->salary_range);
        }

        if ($request->filled('job_type')) {
            $query->where('job_type', $request->job_type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
    }

    /** Apply search filter */
    private function applySearchFilter(Builder $query, string $search): void
    {
        $query->where(function ($q) use ($search) {
            $q->where('job_name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")
                ->orWhere('job_category', 'like', "%{$search}%")
                ->orWhere('location', 'like', "%{$search}%")
                ->orWhere('salary_range', 'like', "%{$search}%")
                ->orWhere('job_type', 'like', "%{$search}%");
        });
    }

    /** Applied filters summary */
    private function getAppliedFilters(Request $request): array
    {
        $filters = [];

        if ($request->filled('job_category')) $filters['job_category'] = $request->job_category;
        if ($request->filled('categories')) $filters['categories'] = is_array($request->categories) ? $request->categories : explode(',', $request->categories);
        if ($request->filled('search')) $filters['search'] = $request->search;
        if ($request->filled('user_id')) $filters['user_id'] = $request->user_id;
        if ($request->filled('location')) $filters['location'] = $request->location;
        if ($request->filled('salary_range')) $filters['salary_range'] = $request->salary_range;
        if ($request->filled('job_type')) $filters['job_type'] = $request->job_type;
        if ($request->filled('date_from')) $filters['date_from'] = $request->date_from;
        if ($request->filled('date_to')) $filters['date_to'] = $request->date_to;

        return $filters;
    }

    /** Filter statistics */
    private function getFilterCounts(): array
    {
        return [
            'total_jobs' => Job::count(),
            'categories_count' => Job::distinct()->count('job_category'),
            'recent_jobs' => Job::where('created_at', '>=', now()->subDays(7))->count()
        ];
    }
}