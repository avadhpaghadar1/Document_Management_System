<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        $userId = Auth::user();
        $userId = $userId->id;
        return [
            'name' => 'required|string|regex:/^[\pL\s\-]+$/u',
            'email' => 'required|email|unique:users,email,' . $userId,
            'mobile' => 'sometimes|required|integer',
            'country' => 'sometimes|required|not_in:2',
            'image' => 'nullable|image'
        ];
    }
}
