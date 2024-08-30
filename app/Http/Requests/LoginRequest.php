<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules\Password as RulesPassword;

class LoginRequest extends FormRequest
{
    protected $stopOnFirstFailure = true;
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
            "username" => ["required", "email", "max:100", "min:3"],
            "password" => ["required", RulesPassword::min(6)->letters()->numbers()->symbols()],
            
        ];
    }

    public function prepareForValidation():void {
        $this->merge([
            "username" => strtolower($this->input("username")),
          
        ]);
    }

    public function passedValidation ():void {
        $this->merge([
            "password" => bcrypt($this->input("password"))
        ]);
    }
}
