<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ActivityLogResource;
use App\Models\ActivityLog;
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
    /**
     * Return a paginated, chronologically-descending list of all activity log entries.
     *
     * Eager-loads the `causer` relation (the {@see User} who triggered the event)
     * and paginates at 25 entries per page. The response is not filterable or
     * sortable beyond the default `latest()` ordering.
     *
     * @return AnonymousResourceCollection<\Illuminate\Contracts\Pagination\LengthAwarePaginator<ActivityLogResource>>
     */
    public function index(): AnonymousResourceCollection
    {
        $activities = ActivityLog::query()
            ->with('causer')
            ->latest()
            ->paginate(25);

        return ActivityLogResource::collection($activities)
            ->additional([
                'success' => true,
                'message' => 'Activity logs retrieved successfully.',
            ]);
    }
}
