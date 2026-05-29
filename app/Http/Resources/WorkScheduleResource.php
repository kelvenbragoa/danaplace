<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkScheduleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'year' => $this->year,
            'month' => $this->month,
            'month_name' => $this->month_name,
            'description' => $this->description,
            'status' => $this->status,
            'auto_generate_days' => $this->auto_generate_days,
            'created_by' => $this->created_by,
            
            // Dates
            'start_date' => $this->start_date?->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
            'days_in_month' => $this->days_in_month,
            
            // Relationships
            'creator' => $this->whenLoaded('creator', function () {
                return [
                    'id' => $this->creator->id,
                    'name' => $this->creator->name,
                ];
            }),
            
            'shifts' => ShiftResource::collection($this->whenLoaded('shifts')),
            'shifts_count' => $this->whenCounted('shifts'),
            
            // Computed attributes
            'is_active' => $this->is_active,
            'can_edit' => $this->can_edit,
            'can_copy' => $this->can_copy,
            
            // Stats (when loaded)
            'stats' => $this->when(isset($this->stats), $this->stats),
            
            // Timestamps
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
