<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class ActivityService
{
    /**
     * Log a create activity.
     */
    public function logCreate(Model $entity, array $metadata = []): Activity
    {
        return $this->createActivity(
            Activity::ACTION_CREATE,
            $entity,
            "Created {$this->getEntityDisplayName($entity)}",
            $metadata
        );
    }

    /**
     * Log an update activity.
     */
    public function logUpdate(Model $entity, array $metadata = []): Activity
    {
        return $this->createActivity(
            Activity::ACTION_UPDATE,
            $entity,
            "Updated {$this->getEntityDisplayName($entity)}",
            $metadata
        );
    }

    /**
     * Log a delete activity.
     */
    public function logDelete(Model $entity, array $metadata = []): Activity
    {
        return $this->createActivity(
            Activity::ACTION_DELETE,
            $entity,
            "Deleted {$this->getEntityDisplayName($entity)}",
            $metadata
        );
    }

    /**
     * Get recent activities for a user.
     */
    public function getRecentActivities(int $userId, int $limit = 10): Collection
    {
        return Activity::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get activity details formatted for display.
     */
    public function getActivityDetails(Activity $activity): array
    {
        return [
            'id' => $activity->id,
            'action' => $activity->getActionVerb() . ' ' . $activity->getEntityName(),
            'details' => $activity->description,
            'time' => $activity->getFormattedTime(),
            'icon' => $activity->getIcon(),
            'color_classes' => $activity->getColorClasses(),
            'metadata' => $activity->metadata,
        ];
    }

    /**
     * Create an activity record.
     */
    protected function createActivity(string $actionType, Model $entity, string $description, array $metadata = []): Activity
    {
        return Activity::create([
            'user_id' => Auth::id(),
            'action_type' => $actionType,
            'entity_type' => $this->getEntityType($entity),
            'entity_id' => $entity->id,
            'description' => $description,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Get the entity type string for the activity.
     */
    protected function getEntityType(Model $entity): string
    {
        $class = get_class($entity);

        return match($class) {
            'App\Models\EmailAccount' => Activity::ENTITY_EMAIL_ACCOUNT,
            'App\Models\MexcAccount' => Activity::ENTITY_MEXC_ACCOUNT,
            'App\Models\Proxy' => Activity::ENTITY_PROXY,
            'App\Models\Web3Wallet' => Activity::ENTITY_WEB3_WALLET,
            'App\Models\User' => Activity::ENTITY_USER,
            'App\Models\MexcReferral' => Activity::ENTITY_MEXC_REFERRAL,
            default => strtolower(class_basename($entity)),
        };
    }

    /**
     * Get a display name for the entity.
     */
    protected function getEntityDisplayName(Model $entity): string
    {
        $class = get_class($entity);

        return match($class) {
            'App\Models\EmailAccount' => "email account ({$entity->email_address})",
            'App\Models\MexcAccount' => "MEXC account ({$entity->emailAccount->email_address})",
            'App\Models\Proxy' => "proxy ({$entity->ip_address}:{$entity->port})",
            'App\Models\Web3Wallet' => "Web3 wallet ({$entity->getFormattedAddress()})",
            'App\Models\User' => "user ({$entity->name})",
            'App\Models\MexcReferral' => "referral connection",
            default => class_basename($entity),
        };
    }
}