<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Services\ActivityService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityController extends Controller
{
    protected $activityService;

    public function __construct(ActivityService $activityService)
    {
        $this->activityService = $activityService;
    }

    /**
     * Display a listing of the user's activities.
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $query = Activity::forUser(auth()->id());

        // Filter by entity type if requested
        if ($request->has('entity_type') && $request->entity_type) {
            $query->where('entity_type', $request->entity_type);
        }

        // Filter by action type if requested
        if ($request->has('action_type') && $request->action_type) {
            $query->where('action_type', $request->action_type);
        }

        // Filter by date range if requested
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Default sorting by newest first
        $activities = $query->orderBy('created_at', 'desc')->paginate(20);

        // Get statistics for filtering UI
        $stats = [
            'total' => Activity::forUser(auth()->id())->count(),
            'by_entity' => Activity::forUser(auth()->id())
                ->selectRaw('entity_type, COUNT(*) as count')
                ->groupBy('entity_type')
                ->pluck('count', 'entity_type')
                ->toArray(),
            'by_action' => Activity::forUser(auth()->id())
                ->selectRaw('action_type, COUNT(*) as count')
                ->groupBy('action_type')
                ->pluck('count', 'action_type')
                ->toArray(),
        ];

        return view('activities.index', [
            'activities' => $activities,
            'stats' => $stats,
            'filters' => $request->only(['entity_type', 'action_type', 'date_from', 'date_to']),
            'entityTypes' => [
                Activity::ENTITY_USER => 'Account Registration',
                Activity::ENTITY_EMAIL_ACCOUNT => 'Email Accounts',
                Activity::ENTITY_PROXY => 'Proxies',
                Activity::ENTITY_MEXC_ACCOUNT => 'MEXC Accounts',
                Activity::ENTITY_WEB3_WALLET => 'Web3 Wallets',
            ],
            'actionTypes' => [
                Activity::ACTION_CREATE => 'Created',
                Activity::ACTION_UPDATE => 'Updated',
                Activity::ACTION_DELETE => 'Deleted',
            ],
        ]);
    }

    /**
     * Get recent activities for AJAX requests.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function recent(Request $request)
    {
        $limit = $request->get('limit', 5);
        $activities = $this->activityService->getRecentActivities(auth()->id(), $limit);

        $formattedActivities = $activities->map(function ($activity) {
            return $this->activityService->getActivityDetails($activity);
        });

        return response()->json([
            'success' => true,
            'activities' => $formattedActivities,
        ]);
    }

    /**
     * Show the form for creating a new activity (for testing purposes).
     */
    public function create(): View
    {
        // This is mainly for testing/admin purposes
        return view('activities.create');
    }

    /**
     * Store a manually created activity (for testing purposes).
     */
    public function store(Request $request)
    {
        $request->validate([
            'action_type' => 'required|in:create,update,delete',
            'entity_type' => 'required|string',
            'description' => 'required|string|max:255',
            'metadata' => 'nullable|array',
        ]);

        $activity = $this->activityService->log(
            $request->action_type,
            $request->entity_type,
            null,
            $request->metadata ?? []
        );

        return redirect()->route('activities.index')
            ->with('success', 'Activity created successfully.');
    }

    /**
     * Display the specified activity.
     */
    public function show(Activity $activity): View
    {
        // Ensure user can only view their own activities
        if ($activity->user_id !== auth()->id()) {
            abort(403);
        }

        return view('activities.show', [
            'activity' => $activity,
            'details' => $this->activityService->getActivityDetails($activity),
        ]);
    }

    /**
     * Remove the specified activity from storage (for admin/testing).
     */
    public function destroy(Activity $activity)
    {
        // Ensure user can only delete their own activities
        if ($activity->user_id !== auth()->id()) {
            abort(403);
        }

        $activity->delete();

        return redirect()->route('activities.index')
            ->with('success', 'Activity deleted successfully.');
    }
}