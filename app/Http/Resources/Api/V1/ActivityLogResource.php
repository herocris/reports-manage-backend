<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Transforms an ActivityLog model into the API JSON shape.
 *
 * Exposes a simplified `causer` inline object (id, name, email) when the
 * `causer` relation is loaded; returns null if no causer is associated
 * (e.g. for system-initiated events).
 */
class ActivityLogResource extends JsonResource
{
    /**
     * Transform the ActivityLog model into the API JSON shape.
     *
     * @param  Request  $request  The current HTTP request.
     * @return array<string, mixed> Shape: {id, log_name, description, event,
     *                              subject_type (base class name), subject_id, causer: {id, name, email}|null,
     *                              properties (JSON), created_at, updated_at}
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'log_name' => $this->log_name,
            'description' => $this->description,
            'event' => $this->event,
            'subject_type' => $this->subject_type ? class_basename($this->subject_type) : null,
            'subject_id' => $this->subject_id,
            'causer' => $this->whenLoaded('causer', fn () => $this->causer ? [
                'id' => $this->causer->id,
                'name' => $this->causer->name,
                'email' => $this->causer->email,
            ] : null),
            'properties' => $this->redactSecrets($this->properties?->toArray() ?? []),
            'attribute_changes' => $this->redactSecrets($this->attribute_changes?->toArray() ?? []),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    /** @param array<string|int, mixed> $values
     * @return array<string|int, mixed>
     */
    private function redactSecrets(array $values): array
    {
        foreach ($values as $key => $value) {
            if (in_array(strtolower((string) $key), ['password', 'password_confirmation', 'current_password', 'remember_token'], true)) {
                unset($values[$key]);
            } elseif (is_array($value)) {
                $values[$key] = $this->redactSecrets($value);
            }
        }

        return $values;
    }
}
