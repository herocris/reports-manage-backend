<?php

namespace App\Support\Query\Definitions;

use App\Support\Query\ActivityLogSubjectTypes;
use Illuminate\Database\Eloquent\Builder;

/**
 * Index query definition for the ActivityLog resource.
 *
 * Configures search, sorting, filters, and pagination defaults.
 */
class ActivityLogIndexDefinition
{
    /**
     * Return the full index configuration array for activity logs.
     *
     * @return array<string, mixed>
     */
    public static function definition(): array
    {
        return [
            'searchable' => ['log_name', 'description', 'event'],
            'sortable' => [
                'id',
                'log_name',
                'description',
                'event',
                'subject_type',
                'subject_id',
                'causer_id',
                'created_at',
                'updated_at',
            ],
            'filters' => [
                'subject_type' => function (Builder $query, string $value): void {
                    $subjectType = ActivityLogSubjectTypes::resolve($value);

                    if ($subjectType === null) {
                        $query->whereKey([]);

                        return;
                    }

                    $query->where('subject_type', $subjectType);
                },
                'subject_id' => 'subject_id',
                'event' => 'event',
            ],
            'default_sort' => 'created_at',
            'default_direction' => 'desc',
            'default_per_page' => 10,
        ];
    }
}
