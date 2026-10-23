<?php

namespace App\Http\Requests\Photo;

use App\Models\Photo;
use Illuminate\Foundation\Http\FormRequest;

class StorePhotoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Photo::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'image' => ['required', 'file', 'mimes:'.implode(',', config('photos.mimes')), 'max:'.config('photos.max_kb')],
            'name' => ['required', 'string', 'max:120'],
            'location' => ['required', 'string', 'max:160'],
            'description' => ['required', 'string'],
        ];
    }
}
