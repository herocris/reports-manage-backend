<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Model;

trait TracksCascadeDeletion
{
    protected static function bootTracksCascadeDeletion(): void
    {
        static::restoring(function (Model $model): void {
            $model->setAttribute('deletion_batch_id', null);
        });
    }
}
