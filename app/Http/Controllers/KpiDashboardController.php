<?php

namespace App\Http\Controllers;

use App\Models\KpiTask;
use App\Models\KpiTarget;
use App\Models\KpiTurtle;
use App\Models\KpiTurtleItem;
use App\Models\MexcAccount;
use App\Models\EmailAccount;
use App\Models\Proxy;
use App\Models\Web3Wallet;
use App\Services\KpiGamificationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class KpiDashboardController extends Controller
{
    protected $kpiService;

    public function __construct(KpiGamificationService $kpiService)
    {
        $this->kpiService = $kpiService;
    }

    /**
     * Display the KPI dashboard.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $turtleData = $this->kpiService->getTurtleDetails($user);

        // Get account creation statistics
        $accountStats = [
            'mexc_accounts' => MexcAccount::where('user_id', $user->id)->count(),
            'email_accounts' => EmailAccount::where('user_id', $user->id)->count(),
            'proxies' => Proxy::where('user_id', $user->id)->count(),
            'web3_wallets' => Web3Wallet::where('user_id', $user->id)->count(),
        ];

        // Get shop items available for purchase
        $shopItems = KpiTurtleItem::available()
            ->level($turtleData['turtle']['level'])
            ->get()
            ->groupBy('type');

        // Get leaderboards
        $levelLeaderboard = $this->kpiService->getLeaderboard('level', 5);
        $loveLeaderboard = $this->kpiService->getLeaderboard('love', 5);

        return view('kpi.dashboard', [
            'turtleData' => $turtleData,
            'accountStats' => $accountStats,
            'shopItems' => $shopItems,
            'levelLeaderboard' => $levelLeaderboard['leaderboard'] ?? [],
            'loveLeaderboard' => $loveLeaderboard['leaderboard'] ?? [],
        ]);
    }

    /**
     * Complete a task.
     */
    public function completeTask(Request $request, int $taskId): JsonResponse
    {
        $user = Auth::user();
        $task = KpiTask::findOrFail($taskId);

        $result = $this->kpiService->processTaskCompletion($user, $task);

        return response()->json($result);
    }

    /**
     * Feed the turtle to convert love to experience.
     */
    public function feedTurtle(Request $request): JsonResponse
    {
        $request->validate([
            'love_points' => 'required|integer|min:1|max:100',
        ]);

        $user = Auth::user();
        $lovePoints = $request->input('love_points');

        $result = $this->kpiService->feedTurtle($user, $lovePoints);

        return response()->json($result);
    }

    /**
     * Buy an item for the turtle.
     */
    public function buyItem(Request $request, int $itemId): JsonResponse
    {
        $user = Auth::user();
        $result = $this->kpiService->purchaseTurtleItem($user, $itemId);

        return response()->json($result);
    }

    /**
     * Equip an item on the turtle.
     */
    public function equipItem(Request $request, int $itemId): JsonResponse
    {
        $user = Auth::user();
        $result = $this->kpiService->equipTurtleItem($user, $itemId);

        return response()->json($result);
    }

    /**
     * Get turtle details.
     */
    public function getTurtleDetails(Request $request): JsonResponse
    {
        $user = Auth::user();
        $result = $this->kpiService->getTurtleDetails($user);

        return response()->json($result);
    }

    /**
     * Display the leaderboard.
     */
    public function leaderboard(Request $request): View
    {
        $type = $request->input('type', 'level');
        $leaderboardData = $this->kpiService->getLeaderboard($type, 20);

        return view('kpi.leaderboard', [
            'leaderboardData' => $leaderboardData,
            'type' => $type,
        ]);
    }

    /**
     * Display the shop.
     */
    public function shop(Request $request): View
    {
        $user = Auth::user();
        $turtleData = $this->kpiService->getTurtleDetails($user);

        $shopItems = KpiTurtleItem::available()
            ->get()
            ->groupBy('type');

        return view('kpi.shop', [
            'turtleData' => $turtleData,
            'shopItems' => $shopItems,
        ]);
    }

    /**
     * Display the turtle care page.
     */
    public function turtleCare(Request $request): View
    {
        $user = Auth::user();
        $turtleData = $this->kpiService->getTurtleDetails($user);

        return view('kpi.turtle-care', [
            'turtleData' => $turtleData,
        ]);
    }

    /**
     * Display the achievements page.
     */
    public function achievements(Request $request): View
    {
        $user = Auth::user();
        $turtleData = $this->kpiService->getTurtleDetails($user);

        // Define all possible achievements
        $allAchievements = [
            'level_5' => [
                'name' => 'Turtle Toddler',
                'description' => 'Reach level 5 with your turtle',
                'icon' => 'fa-star',
                'color' => 'bg-primary',
            ],
            'level_10' => [
                'name' => 'Turtle Teen',
                'description' => 'Reach level 10 with your turtle',
                'icon' => 'fa-star',
                'color' => 'bg-primary',
            ],
            'level_20' => [
                'name' => 'Turtle Adult',
                'description' => 'Reach level 20 with your turtle',
                'icon' => 'fa-star',
                'color' => 'bg-primary',
            ],
            'tasks_10' => [
                'name' => 'Task Master',
                'description' => 'Complete 10 tasks',
                'icon' => 'fa-tasks',
                'color' => 'bg-secondary',
            ],
            'tasks_50' => [
                'name' => 'Task Legend',
                'description' => 'Complete 50 tasks',
                'icon' => 'fa-tasks',
                'color' => 'bg-secondary',
            ],
            'mexc_accounts_5' => [
                'name' => 'Account Creator',
                'description' => 'Create 5 MEXC accounts',
                'icon' => 'fa-wallet',
                'color' => 'bg-success',
            ],
            'mexc_accounts_20' => [
                'name' => 'Account Virtuoso',
                'description' => 'Create 20 MEXC accounts',
                'icon' => 'fa-wallet',
                'color' => 'bg-success',
            ],
            // Additional achievements
            'login_streak_7' => [
                'name' => 'Weekly Warrior',
                'description' => 'Log in for 7 consecutive days',
                'icon' => 'fa-calendar-check',
                'color' => 'bg-info',
            ],
            'first_customize' => [
                'name' => 'Fashionista',
                'description' => 'Customize your turtle for the first time',
                'icon' => 'fa-palette',
                'color' => 'bg-warning',
            ],
            'all_tasks_complete' => [
                'name' => 'Completionist',
                'description' => 'Complete all available tasks in a single day',
                'icon' => 'fa-check-double',
                'color' => 'bg-danger',
            ],
        ];

        // Mark which ones the user has earned
        $userAchievements = collect($turtleData['achievements'] ?? [])
            ->keyBy('key');

        $achievements = collect($allAchievements)->map(function ($achievement, $key) use ($userAchievements) {
            $earned = $userAchievements->has($key);
            $userAchievement = $userAchievements->get($key);

            return array_merge($achievement, [
                'key' => $key,
                'earned' => $earned,
                'awarded_at' => $earned ? $userAchievement['awarded_at'] : null,
            ]);
        });

        return view('kpi.achievements', [
            'turtleData' => $turtleData,
            'achievements' => $achievements,
        ]);
    }

    /**
     * Customize the turtle.
     */
    public function customize(Request $request): View
    {
        $user = Auth::user();
        $turtleData = $this->kpiService->getTurtleDetails($user);

        // Get all owned items grouped by type
        $ownedItems = collect($turtleData['inventory'] ?? [])->groupBy('type');

        return view('kpi.customize', [
            'turtleData' => $turtleData,
            'ownedItems' => $ownedItems,
        ]);
    }

    /**
     * Check for account creation events to award tasks/targets.
     */
    public function checkAccountCreation(Request $request): JsonResponse
    {
        $user = Auth::user();

        // Find the MEXC account creation task
        $task = KpiTask::where('category', 'account_creation')
            ->where('active', true)
            ->first();

        if (!$task) {
            return response()->json([
                'success' => false,
                'message' => 'No active account creation task found',
            ]);
        }

        // Get the latest created account as source
        $latestAccount = MexcAccount::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->first();

        // Process task completion
        $taskResult = $this->kpiService->processTaskCompletion($user, $task, $latestAccount);

        // Check target completion
        $targetResult = $this->kpiService->checkTargetCompletion($user, 'mexc_accounts');

        return response()->json([
            'success' => true,
            'task_result' => $taskResult,
            'target_result' => $targetResult,
        ]);
    }

    /**
     * Rename the turtle.
     */
    public function renameTurtle(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:20|min:2',
        ]);

        $user = Auth::user();
        $turtle = KpiTurtle::where('user_id', $user->id)->first();

        if (!$turtle) {
            return response()->json([
                'success' => false,
                'message' => 'No turtle found',
            ]);
        }

        $turtle->name = $request->input('name');
        $turtle->save();

        return response()->json([
            'success' => true,
            'message' => 'Turtle renamed successfully',
            'name' => $turtle->name,
        ]);
    }
}