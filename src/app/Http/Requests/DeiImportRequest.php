<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DeiImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        // authorization is handled by CheckApiPermission middleware
        return true;
    }

    public function rules(): array
    {
        return [
            '@graph' => ['required', 'array', 'min:1'],
            'iiif_url' => ['nullable', 'string', 'url'],

            'projectId' => [
                'required',
                'integer',
                Rule::exists('Project', 'ProjectId'),
            ],
            'datasetId' => [
                'required',
                'integer',
                Rule::exists('Dataset', 'DatasetId'),
            ],
            'importName' => ['required', 'string'],
        ];
    }

    public function validationData(): array
    {
        return array_merge($this->all(), $this->query());
    }

    public function messages(): array
    {
        return [
            'projectId.required' => 'projectId query parameter is required.',
            'datasetId.required' => 'datasetId query parameter is required.',
            'importName.required' => 'importName query parameter is required.',
            'projectId.exists' => 'The selected projectId is invalid.',
            'datasetId.exists' => 'The selected datasetId is invalid.',
        ];
    }
}
