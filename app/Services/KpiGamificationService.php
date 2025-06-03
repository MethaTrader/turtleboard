<?php

namespace App\Services;

use App\Models\User;
use App\Models\KpiTurtle;
use App\Models\KpiTask;
use App\Models\KpiUserTask;
use App\Models\KpiReward;
use App\Models\KpiTarget;
use App\Models\KpiUserTarget;
use App\Models\KpiTurtleItem;
use App\Models\MexcAccount;
use App\Models\EmailAccount;
use App\Models\Proxy;
use App\Models\Web3Wallet;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class KpiGamificationService
{
    /**
     * Initialize a new turtle for a user if they don't have one
     */
    public function initializeUserTurtle(User $user): KpiTurtle
    {
        return KpiTurtle::firstOrCreate(
            ['user_id' => $user->id],
            [
                'name' => 'Shelly',
                'level' => 1,
                'love_points' => 10, // Start with some love points
                'total_love_earned' => 10,
                'experience' => 0,
                'last_fed_at' => now(),
                'last_interaction_at' => now(),
                'attributes' => ['color' => 'green', 'shell_pattern' => 'default'],
                'achievements' => [],
            ]
        );
    }

    /**
     * Process a task completion event
     */
    public function processTaskCompletion(User $user, KpiTask $task, $source = null): array
    {
        $result = [
            'success' => false,
            'message' => '',
            'rewards' => null,
            'level_up' => false,
            'achievements' => [],
        ];

        try {
            DB::beginTransaction();

            // Get or create the user's turtle
            $turtle = $this->initializeUserTurtle($user);

            // Check if task is already completed for recurring tasks
            $userTask = KpiUserTask::firstOrNew([
                'user_id' => $user->id,
                'kpi_task_id' => $task->id,
            ]);

            // For recurring tasks (daily/weekly), check if we need to reset
            if ($task->type === 'daily' && $userTask->completed_at) {
                // Reset if the last completion was not today
                if (!$userTask->completed_at->isToday()) {
                    $userTask = new KpiUserTask([
                        'user_id' => $user->id,
                        'kpi_task_id' => $task->id,
                        'progress' => 0,
                        'target' => $task->requirements['count'] ?? 1,
                    ]);
                }
            } elseif ($task->type === 'weekly' && $userTask->completed_at) {
                // Reset if the last completion was not this week
                if (!$userTask->completed_at->isSameWeek(now())) {
                    $userTask = new KpiUserTask([
                        'user_id' => $user->id,
                        'kpi_task_id' => $task->id,
                        'progress' => 0,
                        'target' => $task->requirements['count'] ?? 1,
                    ]);
                }
            } elseif ($task->type === 'recurring') {
                // For recurring tasks, always allow completion
                if ($userTask->completed_at) {
                    $userTask = new KpiUserTask([
                        'user_id' => $user->id,
                        'kpi_task_id' => $task->id,
                        'progress' => 0,
                        'target' => $task->requirements['count'] ?? 1,
                    ]);
                }
            }

            // If it's a new task, set up the initial values
            if (!$userTask->exists) {
                $userTask->progress = 0;
                $userTask->target = $task->requirements['count'] ?? 1;
            }

            // For already completed non-recurring tasks, return early
            if ($userTask->isCompleted() && !in_array($task->type, ['daily', 'weekly', 'recurring'])) {
                $result['message'] = 'Task already completed';
                DB::commit();
                return $result;
            }

            // For recurring tasks that are completed, allow new completion
            if ($userTask->isCompleted() && $task->type === 'recurring') {
                $userTask->progress = 0;
                $userTask->completed_at = null;
                $userTask->target = $task->requirements['count'] ?? 1;
            }

            // Increment progress and check if completed
            $wasCompletedNow = $userTask->incrementProgress();
            $userTask->save();

            // If task was just completed, award the rewards
            if ($wasCompletedNow) {
                // Create reward record
                $reward = KpiReward::create([
                    'user_id' => $user->id,
                    'kpi_task_id' => $task->id,
                    'love_points' => $task->love_reward,
                    'experience_points' => $task->experience_reward,
                    'reason' => "Completed task: {$task->name}",
                    'source_type' => $source ? get_class($source) : null,
                    'source_id' => $source ? $source->id : null,
                ]);

                // Update turtle with rewards
                $turtle->addLovePoints($task->love_reward);
                $turtle->addExperience($task->experience_reward);

                // Check for level up
                $levelBefore = $turtle->level;
                while ($turtle->canLevelUp()) {
                    $turtle->processLevelUp();
                }
                $levelUp = $turtle->level > $levelBefore;

                // Check for achievements
                $achievements = $this->checkForAchievements($user, $turtle, $task);

                $result = [
                    'success' => true,
                    'message' => 'Task completed successfully!',
                    'rewards' => [
                        'love' => $task->love_reward,
                        'experience' => $task->experience_reward,
                    ],
                    'level_up' => $levelUp,
                    'achievements' => $achievements,
                ];
            } else {
                $result = [
                    'success' => true,
                    'message' => 'Progress updated',
                    'progress' => $userTask->progress,
                    'target' => $userTask->target,
                    'percentage' => $userTask->getCompletionPercentage(),
                ];
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error processing task completion: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'task_id' => $task->id,
                'exception' => $e,
            ]);

            $result['success'] = false;
            $result['message'] = 'Error processing task: ' . $e->getMessage();
        }

        return $result;
    }

    /**
     * Check for and award achievements based on the completed task
     */
    protected function checkForAchievements(User $user, KpiTurtle $turtle, KpiTask $task): array
    {
        $awardedAchievements = [];

        // Check for level-based achievements
        if ($turtle->level >= 5 && !$turtle->achievements?->has('level_5')) {
            $turtle->awardAchievement('level_5', ['level' => 5]);
            $awardedAchievements[] = [
                'key' => 'level_5',
                'name' => 'Turtle Toddler',
                'description' => 'Reached level 5 with your turtle',
            ];
        }

        if ($turtle->level >= 10 && !$turtle->achievements?->has('level_10')) {
            $turtle->awardAchievement('level_10', ['level' => 10]);
            $awardedAchievements[] = [
                'key' => 'level_10',
                'name' => 'Turtle Teen',
                'description' => 'Reached level 10 with your turtle',
            ];
        }

        // Check for task completion count achievements
        $completedTasksCount = KpiUserTask::where('user_id', $user->id)
            ->whereNotNull('completed_at')
            ->count();

        if ($completedTasksCount >= 10 && !$turtle->achievements?->has('tasks_10')) {
            $turtle->awardAchievement('tasks_10', ['count' => 10]);
            $awardedAchievements[] = [
                'key' => 'tasks_10',
                'name' => 'Task Master',
                'description' => 'Completed 10 tasks',
            ];
        }

        if ($completedTasksCount >= 50 && !$turtle->achievements?->has('tasks_50')) {
            $turtle->awardAchievement('tasks_50', ['count' => 50]);
            $awardedAchievements[] = [
                'key' => 'tasks_50',
                'name' => 'Task Legend',
                'description' => 'Completed 50 tasks',
            ];
        }

        // Check for MEXC account creation achievements
        if ($task->category === 'account_creation') {
            $mexcAccountCount = MexcAccount::where('user_id', $user->id)->count();

            if ($mexcAccountCount >= 5 && !$turtle->achievements?->has('mexc_accounts_5')) {
                $turtle->awardAchievement('mexc_accounts_5', ['count' => 5]);
                $awardedAchievements[] = [
                    'key' => 'mexc_accounts_5',
                    'name' => 'Account Creator',
                    'description' => 'Created 5 MEXC accounts',
                ];
            }

            if ($mexcAccountCount >= 20 && !$turtle->achievements?->has('mexc_accounts_20')) {
                $turtle->awardAchievement('mexc_accounts_20', ['count' => 20]);
                $awardedAchievements[] = [
                    'key' => 'mexc_accounts_20',
                    'name' => 'Account Virtuoso',
                    'description' => 'Created 20 MEXC accounts',
                ];
            }
        }

        return $awardedAchievements;
    }

    /**
     * Process automatic targets completion check
     */
    public function checkTargetCompletion(User $user, string $metricType): array
    {
        $result = [
            'success' => false,
            'message' => '',
            'completed_targets' => [],
        ];

        try {
            // Get active targets for this metric type that are currently in their time period
            $targets = KpiTarget::where('metric_type', $metricType)
                ->active()
                ->current()
                ->get();

            if ($targets->isEmpty()) {
                $result['message'] = 'No active targets found for this metric type';
                return $result;
            }

            $completedTargets = [];

            foreach ($targets as $target) {
                // Get or create user target
                $userTarget = KpiUserTarget::firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'kpi_target_id' => $target->id,
                    ],
                    [
                        'current_value' => 0,
                        'achieved' => false,
                    ]
                );

                // Skip if already achieved
                if ($userTarget->achieved) {
                    continue;
                }

                // Calculate current value based on metric type
                $currentValue = $this->calculateMetricValue($user, $metricType, $target);

                // Update the current value
                $userTarget->current_value = $currentValue;

                // Check if target is achieved
                if ($currentValue >= $target->target_value) {
                    $userTarget->achieved = true;
                    $userTarget->achieved_at = now();
                    $userTarget->save();

                    // Award the rewards
                    $this->awardTargetRewards($user, $target);

                    $completedTargets[] = [
                        'id' => $target->id,
                        'name' => $target->name,
                        'love_reward' => $target->love_reward,
                        'experience_reward' => $target->experience_reward,
                    ];
                } else {
                    // Just save the updated progress
                    $userTarget->save();
                }
            }

            $result = [
                'success' => true,
                'message' => count($completedTargets) > 0
                    ? 'Targets completed successfully!'
                    : 'Targets updated',
                'completed_targets' => $completedTargets,
            ];
        } catch (\Exception $e) {
            Log::error('Error checking target completion: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'metric_type' => $metricType,
                'exception' => $e,
            ]);

            $result['message'] = 'Error checking targets: ' . $e->getMessage();
        }

        return $result;
    }

    /**
     * Calculate the current value for a metric type
     * FIXED: Better date handling for current targets
     */
    protected function calculateMetricValue(User $user, string $metricType, KpiTarget $target): int
    {
        $startDate = $target->start_date;
        $endDate = $target->end_date;

        // For current period tracking, adjust dates if needed
        $now = Carbon::now();

        // If the target period has started but the end date is in the future,
        // we should count from start date to now
        if ($startDate <= $now && $endDate >= $now) {
            // Count within the target period
            $countStartDate = $startDate;
            $countEndDate = $now;
        } else {
            // Use the original range
            $countStartDate = $startDate;
            $countEndDate = $endDate;
        }

        switch ($metricType) {
            case 'mexc_accounts':
                return MexcAccount::where('user_id', $user->id)
                    ->whereBetween('created_at', [$countStartDate, $countEndDate])
                    ->count();

            case 'email_accounts':
                return EmailAccount::where('user_id', $user->id)
                    ->whereBetween('created_at', [$countStartDate, $countEndDate])
                    ->count();

            case 'proxies':
                return Proxy::where('user_id', $user->id)
                    ->whereBetween('created_at', [$countStartDate, $countEndDate])
                    ->count();

            case 'web3_wallets':
                return Web3Wallet::where('user_id', $user->id)
                    ->whereBetween('created_at', [$countStartDate, $countEndDate])
                    ->count();

            case 'completed_tasks':
                return KpiUserTask::where('user_id', $user->id)
                    ->whereNotNull('completed_at')
                    ->whereBetween('completed_at', [$countStartDate, $countEndDate])
                    ->count();

            default:
                return 0;
        }
    }

    /**
     * Award rewards for completing a target
     */
    protected function awardTargetRewards(User $user, KpiTarget $target): void
    {
        // Get or create user turtle
        $turtle = $this->initializeUserTurtle($user);

        // Create reward record
        KpiReward::create([
            'user_id' => $user->id,
            'love_points' => $target->love_reward,
            'experience_points' => $target->experience_reward,
            'reason' => "Completed target: {$target->name}",
            'source_type' => KpiTarget::class,
            'source_id' => $target->id,
        ]);

        // Update turtle with rewards
        $turtle->addLovePoints($target->love_reward);
        $turtle->addExperience($target->experience_reward);

        // Process level ups
        while ($turtle->canLevelUp()) {
            $turtle->processLevelUp();
        }
    }

    /**
     * Feed the turtle to convert love to experience
     */
    public function feedTurtle(User $user, int $lovePoints = 5): array
    {
        $result = [
            'success' => false,
            'message' => '',
        ];

        try {
            // Get the user's turtle
            $turtle = KpiTurtle::where('user_id', $user->id)->first();

            if (!$turtle) {
                $result['message'] = 'No turtle found for this user';
                return $result;
            }

            // Check if the user has enough love points
            if ($turtle->love_points < $lovePoints) {
                $result['message'] = 'Not enough love points';
                return $result;
            }

            // Feed the turtle
            $fed = $turtle->feed($lovePoints);

            if ($fed) {
                // Check for level up
                $levelBefore = $turtle->level;
                while ($turtle->canLevelUp()) {
                    $turtle->processLevelUp();
                }
                $levelUp = $turtle->level > $levelBefore;

                $result = [
                    'success' => true,
                    'message' => 'Turtle fed successfully!',
                    'love_points_remaining' => $turtle->love_points,
                    'experience_gained' => $lovePoints,
                    'level_up' => $levelUp,
                    'new_level' => $turtle->level,
                    'happiness' => $turtle->getHappinessLevel(),
                    'mood' => $turtle->getMood(),
                ];
            } else {
                $result['message'] = 'Failed to feed turtle';
            }
        } catch (\Exception $e) {
            Log::error('Error feeding turtle: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'love_points' => $lovePoints,
                'exception' => $e,
            ]);

            $result['message'] = 'Error feeding turtle: ' . $e->getMessage();
        }

        return $result;
    }

    /**
     * Purchase an item for the turtle
     */
    public function purchaseTurtleItem(User $user, int $itemId): array
    {
        $result = [
            'success' => false,
            'message' => '',
        ];

        try {
            DB::beginTransaction();

            // Get the user's turtle
            $turtle = KpiTurtle::where('user_id', $user->id)->first();

            if (!$turtle) {
                $result['message'] = 'No turtle found for this user';
                DB::rollBack();
                return $result;
            }

            // Get the item
            $item = KpiTurtleItem::findOrFail($itemId);

            // Check if the turtle already has this item
            $existingItem = $turtle->items()->where('kpi_turtle_item_id', $itemId)->first();
            if ($existingItem) {
                $result['message'] = 'Turtle already has this item';
                DB::rollBack();
                return $result;
            }

            // Check if the turtle has enough love points
            if ($turtle->love_points < $item->love_cost) {
                $result['message'] = 'Not enough love points';
                DB::rollBack();
                return $result;
            }

            // Check if the turtle meets the level requirement
            if ($turtle->level < $item->required_level) {
                $result['message'] = "Turtle level too low. Required: {$item->required_level}";
                DB::rollBack();
                return $result;
            }

            // Deduct love points
            $turtle->love_points -= $item->love_cost;
            $turtle->save();

            // Attach the item to the turtle
            $turtle->items()->attach($item->id, [
                'equipped' => false,
                'purchased_at' => now(),
            ]);

            DB::commit();

            $result = [
                'success' => true,
                'message' => "Successfully purchased {$item->name}!",
                'item' => $item,
                'love_points_remaining' => $turtle->love_points,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error purchasing turtle item: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'item_id' => $itemId,
                'exception' => $e,
            ]);

            $result['message'] = 'Error purchasing item: ' . $e->getMessage();
        }

        return $result;
    }

    /**
     * Equip an item on the turtle
     */
    public function equipTurtleItem(User $user, int $itemId): array
    {
        $result = [
            'success' => false,
            'message' => '',
        ];

        try {
            DB::beginTransaction();

            // Get the user's turtle
            $turtle = KpiTurtle::where('user_id', $user->id)->first();

            if (!$turtle) {
                $result['message'] = 'No turtle found for this user';
                DB::rollBack();
                return $result;
            }

            // Get the item
            $item = KpiTurtleItem::findOrFail($itemId);

            // Check if the turtle has this item
            $hasItem = $turtle->items()
                ->where('kpi_turtle_item_id', $itemId)
                ->exists();

            if (!$hasItem) {
                $result['message'] = 'Turtle does not own this item';
                DB::rollBack();
                return $result;
            }

            // Unequip any currently equipped items of the same type
            $turtle->items()
                ->wherePivot('equipped', true)
                ->where('type', $item->type)
                ->each(function ($equippedItem) use ($turtle) {
                    $turtle->items()->updateExistingPivot(
                        $equippedItem->id,
                        ['equipped' => false]
                    );
                });

            // Equip the new item
            $turtle->items()->updateExistingPivot(
                $itemId,
                ['equipped' => true]
            );

            DB::commit();

            $result = [
                'success' => true,
                'message' => "{$item->name} equipped successfully!",
                'item' => $item,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error equipping turtle item: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'item_id' => $itemId,
                'exception' => $e,
            ]);

            $result['message'] = 'Error equipping item: ' . $e->getMessage();
        }

        return $result;
    }

    /**
     * Get user's turtle details with all related data
     */
    public function getTurtleDetails(User $user): array
    {
        try {
            // Get or create the user's turtle
            $turtle = $this->initializeUserTurtle($user);

            // Load relationships
            $turtle->load('items');

            // Get active tasks
            $tasks = KpiTask::active()->get();

            // Get user tasks
            $userTasks = KpiUserTask::where('user_id', $user->id)
                ->with('task')
                ->get()
                ->keyBy('kpi_task_id');

            // Format tasks with progress
            $formattedTasks = $tasks->map(function ($task) use ($userTasks) {
                $userTask = $userTasks->get($task->id);

                return [
                    'id' => $task->id,
                    'name' => $task->name,
                    'description' => $task->description,
                    'type' => $task->type,
                    'category' => $task->category,
                    'love_reward' => $task->love_reward,
                    'experience_reward' => $task->experience_reward,
                    'progress' => $userTask ? $userTask->progress : 0,
                    'target' => $userTask ? $userTask->target : ($task->requirements['count'] ?? 1),
                    'completed' => $userTask ? $userTask->isCompleted() : false,
                    'completed_at' => $userTask && $userTask->completed_at ? $userTask->completed_at->format('Y-m-d H:i:s') : null,
                    'percentage' => $userTask ? $userTask->getCompletionPercentage() : 0,
                ];
            });

            // Get active targets
            $targets = KpiTarget::active()->current()->get();

            // Get user targets
            $userTargets = KpiUserTarget::where('user_id', $user->id)
                ->with('target')
                ->get()
                ->keyBy('kpi_target_id');

            // Format targets with progress
            $formattedTargets = $targets->map(function ($target) use ($userTargets, $user) {
                $userTarget = $userTargets->get($target->id);

                if (!$userTarget) {
                    // Calculate the current value
                    $currentValue = $this->calculateMetricValue($user, $target->metric_type, $target);

                    // Create a new user target
                    $userTarget = new KpiUserTarget([
                        'user_id' => $user->id,
                        'kpi_target_id' => $target->id,
                        'current_value' => $currentValue,
                        'achieved' => $currentValue >= $target->target_value,
                        'achieved_at' => $currentValue >= $target->target_value ? now() : null,
                    ]);
                    $userTarget->save();
                }

                return [
                    'id' => $target->id,
                    'name' => $target->name,
                    'description' => $target->description,
                    'metric_type' => $target->metric_type,
                    'target_value' => $target->target_value,
                    'love_reward' => $target->love_reward,
                    'experience_reward' => $target->experience_reward,
                    'period_type' => $target->period_type,
                    'start_date' => $target->start_date->format('Y-m-d'),
                    'end_date' => $target->end_date->format('Y-m-d'),
                    'current_value' => $userTarget->current_value,
                    'achieved' => $userTarget->achieved,
                    'achieved_at' => $userTarget->achieved_at ? $userTarget->achieved_at->format('Y-m-d H:i:s') : null,
                    'percentage' => $userTarget->getProgressPercentage(),
                ];
            });

            // Format equipped items
            $equippedItems = $turtle->items()
                ->wherePivot('equipped', true)
                ->get()
                ->groupBy('type')
                ->map(function ($items) {
                    return $items->first();
                });

            // Format inventory items
            $inventoryItems = $turtle->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'description' => $item->description,
                    'type' => $item->type,
                    'image_path' => $item->image_path,
                    'equipped' => $item->pivot->equipped,
                    'purchased_at' => Carbon::parse($item->pivot->purchased_at)->format('Y-m-d H:i:s'),
                ];
            });

            // Format achievements
            $achievements = collect($turtle->achievements ?? [])->map(function ($achievement, $key) {
                $name = match($key) {
                    'level_5' => 'Turtle Toddler',
                    'level_10' => 'Turtle Teen',
                    'level_20' => 'Turtle Adult',
                    'tasks_10' => 'Task Master',
                    'tasks_50' => 'Task Legend',
                    'mexc_accounts_5' => 'Account Creator',
                    'mexc_accounts_20' => 'Account Virtuoso',
                    default => ucfirst(str_replace('_', ' ', $key)),
                };

                return [
                    'key' => $key,
                    'name' => $name,
                    'awarded_at' => Carbon::parse($achievement['awarded_at'])->format('Y-m-d H:i:s'),
                    'metadata' => $achievement['metadata'],
                ];
            })->values();

            // Calculate next level experience requirements
            $nextLevelExp = $turtle->experienceForNextLevel();
            $expPercentage = $nextLevelExp > 0
                ? min(100, (int)(($turtle->experience / $nextLevelExp) * 100))
                : 0;

            // Recent rewards
            $recentRewards = KpiReward::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($reward) {
                    return [
                        'id' => $reward->id,
                        'love_points' => $reward->love_points,
                        'experience_points' => $reward->experience_points,
                        'reason' => $reward->reason,
                        'created_at' => $reward->created_at->format('Y-m-d H:i:s'),
                    ];
                });

            return [
                'success' => true,
                'turtle' => [
                    'id' => $turtle->id,
                    'name' => $turtle->name,
                    'level' => $turtle->level,
                    'love_points' => $turtle->love_points,
                    'total_love_earned' => $turtle->total_love_earned,
                    'experience' => $turtle->experience,
                    'next_level_exp' => $nextLevelExp,
                    'exp_percentage' => $expPercentage,
                    'last_fed_at' => $turtle->last_fed_at ? $turtle->last_fed_at->format('Y-m-d H:i:s') : null,
                    'last_interaction_at' => $turtle->last_interaction_at ? $turtle->last_interaction_at->format('Y-m-d H:i:s') : null,
                    'happiness' => $turtle->getHappinessLevel(),
                    'mood' => $turtle->getMood(),
                    'attributes' => $turtle->attributes,
                    'equipped_items' => $equippedItems,
                ],
                'tasks' => $formattedTasks,
                'targets' => $formattedTargets,
                'inventory' => $inventoryItems,
                'achievements' => $achievements,
                'recent_rewards' => $recentRewards,
                'message' => 'Turtle details retrieved successfully',
            ];
        } catch (\Exception $e) {
            Log::error('Error getting turtle details: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'exception' => $e,
            ]);

            return [
                'success' => false,
                'message' => 'Error getting turtle details: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get leaderboard data
     */
    public function getLeaderboard(string $type = 'level', int $limit = 10): array
    {
        try {
            $query = KpiTurtle::with('user');

            switch ($type) {
                case 'level':
                    $query->orderBy('level', 'desc')
                        ->orderBy('experience', 'desc');
                    break;

                case 'love':
                    $query->orderBy('total_love_earned', 'desc');
                    break;

                case 'achievements':
                    $query->orderByRaw('JSON_LENGTH(achievements) DESC');
                    break;

                default:
                    $query->orderBy('level', 'desc');
            }

            $leaderboard = $query->limit($limit)->get()->map(function ($turtle, $index) use ($type) {
                return [
                    'rank' => $index + 1,
                    'user_id' => $turtle->user_id,
                    'user_name' => $turtle->user->name,
                    'turtle_name' => $turtle->name,
                    'level' => $turtle->level,
                    'total_love' => $turtle->total_love_earned,
                    'achievements_count' => $turtle->achievements ? count($turtle->achievements) : 0,
                    'value' => match($type) {
                        'level' => $turtle->level,
                        'love' => $turtle->total_love_earned,
                        'achievements' => $turtle->achievements ? count($turtle->achievements) : 0,
                        default => $turtle->level,
                    },
                ];
            });

            return [
                'success' => true,
                'leaderboard_type' => $type,
                'leaderboard' => $leaderboard,
                'message' => 'Leaderboard retrieved successfully',
            ];
        } catch (\Exception $e) {
            Log::error('Error getting leaderboard: ' . $e->getMessage(), [
                'type' => $type,
                'exception' => $e,
            ]);

            return [
                'success' => false,
                'message' => 'Error getting leaderboard: ' . $e->getMessage(),
            ];
        }
    }
}