<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProfileRequest extends FormRequest
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
            'profile_image' => ['nullable', 'image', 'mimes:jpeg,png'],
            'name' => ['required', 'max:20'],
            'postal_code' => ['required', 'regex:/^[0-9]{3}-[0-9]{4}$/'],
            'address' => ['required']
        ];
    }

    public function messages()
    {
        return [
            'profile_image.mimes' => 'プロフィール画像の拡張子は.jpegもしくは.pngでアップロードしてください',
            'profile_image.image' => 'プロフィール画像の拡張子は.jpegもしくは.pngでアップロードしてください',
            'name.required' => 'お名前を入力してください',
            'name.max' => 'お名前は20文字以下で入力してください',
            'postal_code.required' => '郵便番号を入力してください',
            'postal_code.regex' => '郵便番号はハイフンありの8文字で入力してください',
            'address.required' => '住所を入力してください',
        ];
    }
}
