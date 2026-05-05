<?php

namespace App\Models;

use Spatie\Activitylog\Models\Activity;

/**
 * Extends Spatie Activitylog's Activity model to represent audit trail entries.
 *
 * Every create/update/delete event on an auditable model is stored here.
 * The `causer` morphic relation links the entry to the {@see User} who
 * triggered the action. The `subject` relation links to the affected model.
 * The `properties` column holds a JSON snapshot of old and new attribute values.
 */
class ActivityLog extends Activity
{
    //
}
