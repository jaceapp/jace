<?php

namespace JaceApp\Jace\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LogMessageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => 'required|string',
            'uid' => 'required|string',
            'userId' => 'integer|nullable',
            'guestId' => 'integer|nullable',
            'message' => 'required|string',
            'blocks' => 'nullable',
        ];
    }
}
