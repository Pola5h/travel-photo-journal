<?php

namespace App\Http\Requests\Photo;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePhotoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'location' => ['required', 'string', 'max:160'],
            'description' => ['required', 'string'],
        ];
    }
}
