<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'username' => 'required|string|min:1',
            'password' => 'required|string|min:1'
        ];
    }
    public function messages()
    {
        return [
            'username.required' => 'Credenciais inválidas',
            'username.string' => 'Credenciais inválidas',
            'username.min' => 'Credenciais inválidas',
            'password.required' => 'Credenciais inválidas',
            'password.string' => 'Credenciais inválidas',
            'password.min' => 'Credenciais inválidas',
        ];
    }

    protected function failedValidation(Validator $validator){
        $errors = $validator->errors()->all();
        $firstError = $errors[0];

        throw new HttpResponseException(response()->json([
            'message' => $firstError
        ], 422));
        
    }

}
