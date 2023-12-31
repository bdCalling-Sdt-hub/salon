<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ServiceRequest extends FormRequest
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
            'providerId' => ['required'],
            'serviceName' => ['required'],
            'description' => ['required'],
            'servicePhotoGellary' => ['required'],
            'serviceOur' => ['required'],
            'serviceCharge' => ['required'],
            'homServiceCharge' => ['required'],
            'bookingMony' => ['required'],
            'serviceHour' => ['required'],
        ];
    }
}
