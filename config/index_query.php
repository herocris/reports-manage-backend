<?php

return [
    'allow_only_trashed' => true,
    'allow_legacy_filters' => false,
    'literal_search' => false,
    'qualify_columns' => false,
    'preserve_computed_fields' => true,
    'sort_parameter' => 'sort',
    'direction_parameter' => 'direction',
    'sortable_definition_key' => 'sortable',
    'default_sort' => 'created_at',
    'tie_break_direction' => null,
    'default_per_page' => 15,
    'use_validated_page' => false,
    'append_query_string' => true,
];
