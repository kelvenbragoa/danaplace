<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContractType;
use Illuminate\Http\Request;

class ContractTypeController extends Controller
{
    public function index()
    {
        $searchQuery = request('query');

        $contractTypes = ContractType::query()
            ->when($searchQuery, function ($query, $searchQuery) {
                $query->where('name', 'like', "%{$searchQuery}%");
            })
            ->withCount('technicians')
            ->orderBy('name', 'asc')
            ->paginate();

        return $contractTypes;
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'extra_fields' => 'nullable|array',
            'extra_fields.*.key' => 'required_with:extra_fields|string|max:100',
            'extra_fields.*.label' => 'required_with:extra_fields|string|max:255',
            'extra_fields.*.type' => 'required_with:extra_fields|in:text,number,date,textarea',
            'extra_fields.*.required' => 'nullable|boolean',
            'status' => 'nullable|integer',
        ]);

        $data['extra_fields'] = $this->normalizeExtraFields($data['extra_fields'] ?? []);
        $data['status'] = $data['status'] ?? 1;

        ContractType::create($data);

        return ['message' => 'success'];
    }

    public function show(string $id)
    {
        $contractType = ContractType::withCount('technicians')->findOrFail($id);

        return ['contract_type' => $contractType];
    }

    public function edit(string $id)
    {
        return ContractType::findOrFail($id);
    }

    public function update(Request $request, string $id)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'extra_fields' => 'nullable|array',
            'extra_fields.*.key' => 'required_with:extra_fields|string|max:100',
            'extra_fields.*.label' => 'required_with:extra_fields|string|max:255',
            'extra_fields.*.type' => 'required_with:extra_fields|in:text,number,date,textarea',
            'extra_fields.*.required' => 'nullable|boolean',
            'status' => 'nullable|integer',
        ]);

        $contractType = ContractType::findOrFail($id);
        $data['extra_fields'] = $this->normalizeExtraFields($data['extra_fields'] ?? []);

        $contractType->update($data);

        return $contractType;
    }

    public function destroy(string $id)
    {
        $contractType = ContractType::findOrFail($id);
        $contractType->delete();

        return true;
    }

    private function normalizeExtraFields(array $extraFields): array
    {
        return collect($extraFields)
            ->filter(fn ($field) => !empty($field['key']) && !empty($field['label']))
            ->map(function ($field) {
                return [
                    'key' => $field['key'],
                    'label' => $field['label'],
                    'type' => $field['type'] ?? 'text',
                    'required' => filter_var($field['required'] ?? false, FILTER_VALIDATE_BOOLEAN),
                ];
            })
            ->values()
            ->all();
    }
}
