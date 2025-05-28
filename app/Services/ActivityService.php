<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\EmailAccount;
use App\Models\MexcAccount;
use App\Models\Proxy;
use App\Models\User;
use App\Models\Web3Wallet;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ActivityService
{
    /**
     * Log an activity for the authenticated user.
     *
     * @param string $actionType
     * @param string $entityType
     * @param Model|null $entity
     * @param array $metadata
     * @return Activity
     */
    public function log(
        string $actionType,
        string $entityType,
        ?Model $entity = null,
        array $metadata = []
    ): Activity {
        $description = $this->generateDescription($actionType, $entityType, $entity, $metadata);

        $activity = Activity::create([
            'user_id' => Auth::id(),
            'action_type' => $actionType,
            'entity_type' => $entityType,
            'entity_id' => $entity?->id,
            'description' => $description,
            'metadata' => $metadata,
        ]);

        Log::info('Activity logged', [
            'activity_id' => $activity->id,
            'user_id' => Auth::id(),
            'action' => $actionType,
            'entity' => $entityType,
            'entity_id' => $entity?->id,
        ]);

        return $activity;
    }

    /**
     * Log a create activity.
     *
     * @param Model $entity
     * @param array $metadata
     * @return Activity
     */
    public function logCreate(Model $entity, array $metadata = []): Activity
    {
        return $this->log(
            Activity::ACTION_CREATE,
            $this->getEntityType($entity),
            $entity,
            $metadata
        );
    }

    /**
     * Log an update activity.
     *
     * @param Model $entity
     * @param array $metadata
     * @return Activity
     */
    public function logUpdate(Model $entity, array $metadata = []): Activity
    {
        return $this->log(
            Activity::ACTION_UPDATE,
            $this->getEntityType($entity),
            $entity,
            $metadata
        );
    }

    /**
     * Log a delete activity.
     *
     * @param Model $entity
     * @param array $metadata
     * @return Activity
     */
    public function logDelete(Model $entity, array $metadata = []): Activity
    {
        return $this->log(
            Activity::ACTION_DELETE,
            $this->getEntityType($entity),
            $entity,
            $metadata
        );
    }

    /**
     * Log user registration.
     *
     * @param User $user
     * @return Activity
     */
    public function logUserRegistration(User $user): Activity
    {
        return $this->log(
            Activity::ACTION_CREATE,
            Activity::ENTITY_USER,
            null, // User registration doesn't have an entity_id reference
            ['email' => $user->email, 'name' => $user->name]
        );
    }

    /**
     * Get the recent activities for a user.
     *
     * @param int|null $userId
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRecentActivities(?int $userId = null, int $limit = 5)
    {
        $userId = $userId ?? Auth::id();

        return Activity::forUser($userId)
            ->recent($limit)
            ->get();
    }

    /**
     * Get all activities for a user with pagination.
     *
     * @param int|null $userId
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getUserActivities(?int $userId = null, int $perPage = 20)
    {
        $userId = $userId ?? Auth::id();

        return Activity::forUser($userId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Generate a human-readable description for the activity.
     *
     * @param string $actionType
     * @param string $entityType
     * @param Model|null $entity
     * @param array $metadata
     * @return string
     */
    protected function generateDescription(
        string $actionType,
        string $entityType,
        ?Model $entity = null,
        array $metadata = []
    ): string {
        $actionVerb = match($actionType) {
            Activity::ACTION_CREATE => 'Created',
            Activity::ACTION_UPDATE => 'Updated',
            Activity::ACTION_DELETE => 'Deleted',
            default => 'Modified',
        };

        $entityName = match($entityType) {
            Activity::ENTITY_USER => 'Account',
            Activity::ENTITY_EMAIL_ACCOUNT => 'Email Account',
            Activity::ENTITY_PROXY => 'Proxy',
            Activity::ENTITY_MEXC_ACCOUNT => 'MEXC Account',
            Activity::ENTITY_WEB3_WALLET => 'Web3 Wallet',
            Activity::ENTITY_BALANCE => 'Balance',
            Activity::ENTITY_KPI_GOAL => 'KPI Goal',
            default => 'Item',
        };

        // Special case for user registration
        if ($entityType === Activity::ENTITY_USER && $actionType === Activity::ACTION_CREATE && !$entity) {
            return 'Registered Account';
        }

        return "{$actionVerb} {$entityName}";
    }

    /**
     * Get the entity type string from a model instance.
     *
     * @param Model $entity
     * @return string
     */
    protected function getEntityType(Model $entity): string
    {
        return match(get_class($entity)) {
            User::class => Activity::ENTITY_USER,
            EmailAccount::class => Activity::ENTITY_EMAIL_ACCOUNT,
            Proxy::class => Activity::ENTITY_PROXY,
            MexcAccount::class => Activity::ENTITY_MEXC_ACCOUNT,
            Web3Wallet::class => Activity::ENTITY_WEB3_WALLET,
            default => 'unknown',
        };
    }

    /**
     * Get activity details for display.
     *
     * @param Activity $activity
     * @return array
     */
    public function getActivityDetails(Activity $activity): array
    {
        $details = [
            'id' => $activity->id,
            'action' => $activity->description,
            'time' => $activity->getFormattedTime(),
            'icon' => $activity->getIcon(),
            'color_classes' => $activity->getColorClasses(),
            'metadata' => $activity->metadata,
        ];

        // Add entity-specific details
        switch ($activity->entity_type) {
            case Activity::ENTITY_EMAIL_ACCOUNT:
                $details['details'] = $activity->metadata['email_address'] ?? 'Unknown email';
                break;

            case Activity::ENTITY_PROXY:
                $proxy = $activity->metadata;
                $details['details'] = ($proxy['ip_address'] ?? 'Unknown') . ':' . ($proxy['port'] ?? '0');
                break;

            case Activity::ENTITY_MEXC_ACCOUNT:
                $details['details'] = $activity->metadata['email_address'] ?? 'Unknown email';
                break;

            case Activity::ENTITY_WEB3_WALLET:
                $address = $activity->metadata['address'] ?? 'Unknown';
                $details['details'] = strlen($address) > 10
                    ? substr($address, 0, 6) . '...' . substr($address, -4)
                    : $address;
                break;

            case Activity::ENTITY_USER:
                $details['details'] = $activity->metadata['email'] ?? 'Account registration';
                break;

            default:
                $details['details'] = 'Activity details';
        }

        return $details;
    }

    /**
     * Clean up old activities (optional - for data management).
     *
     * @param int $daysOld
     * @return int Number of deleted activities
     */
    public function cleanupOldActivities(int $daysOld = 90): int
    {
        $cutoffDate = now()->subDays($daysOld);

        return Activity::where('created_at', '<', $cutoffDate)->delete();
    }
}