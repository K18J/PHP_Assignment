<?php

namespace Modules\Cms\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $pageId = (int) ($this->route('page') ?? optional($this->route('comment'))->page_id ?? $this->input('page_id'));

        return [
            'content' => ['required', 'string', 'min:3', 'max:5000'],
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('comments', 'id')->where('page_id', $pageId),
            ],
            'status' => ['sometimes', 'string', 'in:pending,approved,rejected'],
        ];
    }
}

