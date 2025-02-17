<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DeleteTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('delete', $this->task);
    }

    public function rules(): array
    {
        return []; 
    }
}
