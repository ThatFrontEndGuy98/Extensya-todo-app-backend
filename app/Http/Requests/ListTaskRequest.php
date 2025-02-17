<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ListTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['sometimes', 'string', 'in:pending,in_progress,completed'],
            'priority' => ['sometimes', 'string', 'in:low,medium,high'],
            'search' => ['sometimes', 'string', 'max:255'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'sort_by' => ['sometimes', 'string', 'in:created_at,due_date,priority'],
            'sort_direction' => ['sometimes', 'string', 'in:asc,desc'],
        ];
    }

    public function messages(): array
    {
        return [
            'per_page.max' => 'You cannot request more than 100 items per page.',
            'sort_by.in' => 'You can only sort by created_at, due_date, or priority.',
        ];
    }
}
