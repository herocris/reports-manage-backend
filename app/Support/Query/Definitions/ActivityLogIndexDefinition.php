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
            'soft_deletes' => false,
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
                'subject_type' => [
                    'rules' => ['sometimes', 'string', 'max:255'],
                    'query' => function (Builder $query, string $value): void {
                        $subjectType = ActivityLogSubjectTypes::resolve($value);

                        if ($subjectType === null) {
                            $query->whereKey([]);

                            return;
                        }

                        $query->where('subject_type', $subjectType);
                    },
                ],
                'subject_id' => [
                    'rules' => ['sometimes', 'integer', 'min:1'],
                    'query' => 'subject_id',
                ],
                'event' => [
                    'rules' => ['sometimes', 'string', 'max:255'],
                    'query' => 'event',
                ],
            ],
            'default_sort' => 'created_at',
            'default_direction' => 'desc',
            'default_per_page' => 10,
        ];
    }
}
