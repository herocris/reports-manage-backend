<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AppliesIndexQuery;

/**
 * Abstract base controller for all API controllers in this application.
 *
 * All V1 API controllers extend this class. It intentionally contains no
 * logic of its own; shared behavior is injected via traits (e.g.
 * {@see AppliesIndexQuery}) rather than
 * inheritance, keeping the base class a clean extension point.
 */
abstract class Controller
{
    //
}
