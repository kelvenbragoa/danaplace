<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShiftResource extends JsonResource
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
            'work_schedule_id' => $this->work_schedule_id,
            'date' => $this->date?->format('Y-m-d'),
            'name' => $this->name,
            'shift_type' => $this->shift_type,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'description' => $this->description,
            'status' => $this->status,
            
            // Relationships
            'work_schedule' => $this->whenLoaded('workSchedule', function () {
                return [
                    'id' => $this->workSchedule->id,
                    'name' => $this->workSchedule->name,
                    'year' => $this->workSchedule->year,
                    'month' => $this->workSchedule->month,
                ];
            }),
            
            'technicians' => $this->whenLoaded('technicians', function () {
                return $this->technicians->map(function ($technician) {
                    return [
                        'id' => $technician->id,
                        'name' => $technician->name,
                        'email' => $technician->email,
                        'department' => $technician->department?->name ?? null,
                    ];
                });
            }),
            
            // Computed attributes
            'calculated_status' => $this->calculated_status,
            'status_label' => $this->status_label,
            'shift_type_icon' => $this->shift_type_icon,
            'shift_type_label' => $this->shift_type_label,
            'duration' => $this->duration,
            'can_edit' => $this->can_edit,
            'can_delete' => $this->can_delete,
            'can_toggle_status' => $this->can_toggle_status,
            'is_active_now' => $this->is_active_now,
            
            // Schedule info (when included from query)
            'schedule_name' => $this->when(isset($this->schedule_name), $this->schedule_name),
            'schedule_month' => $this->when(isset($this->schedule_month), $this->schedule_month),
            'schedule_year' => $this->when(isset($this->schedule_year), $this->schedule_year),
            
            // Timestamps
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
