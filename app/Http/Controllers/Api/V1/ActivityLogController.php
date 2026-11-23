<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Concerns\AppliesIndexQuery;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ActivityLog\IndexActivityLogRequest;
use App\Http\Resources\Api\V1\ActivityLogResource;
use App\Models\ActivityLog;
use App\Support\Query\Definitions\ActivityLogIndexDefinition;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Handles listing of activity log entries (read-only).
 *
 * Activity log entries are created automatically by Spatie Activitylog whenever
 * a model is created, updated, or deleted. This endpoint provides a paginated,
 * chronologically-descending view of all log entries including the user who
 * triggered each event (the "causer").
 *
 * Access is restricted to users with the `activity-log.view` permission,
 * enforced by route middleware.
 */
class ActivityLogController extends Controller
{
    use AppliesIndexQuery;

    /**
     * Return a paginated, chronologically-descending list of all activity log entries.
     *
     * Eager-loads the `causer` relation (the {@see User} who triggered the event)
     * and applies validated pagination, sorting, search, and filters.
     *
     * @param  IndexActivityLogRequest  $request  Validated query parameters.
     * @return AnonymousResourceCollection<LengthAwarePaginator<ActivityLogResource>>
     */
    public function index(IndexActivityLogRequest $request): AnonymousResourceCollection
    {
        $activities = $this->applyIndexQuery(
            ActivityLog::query()->with('causer'),
            $request->validated(),
            ActivityLogIndexDefinition::definition(),
        );

        return ActivityLogResource::collection($activities)
            ->additional([
                'success' => true,
                'message' => 'Activity logs retrieved successfully.',
            ]);
    }
}
