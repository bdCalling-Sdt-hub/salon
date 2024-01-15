<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProviderRequest extends FormRequest
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
            'catId' => ['required'],
            'businessName' => ['required'],
            'address' => ['required'],
            'description' => ['required'],
            'serviceOur' => ['required'],
            'photoGellary' => ['required'],
            'coverPhoto' => ['required'],
            'phone' => ['required']
        ];
    }
}
