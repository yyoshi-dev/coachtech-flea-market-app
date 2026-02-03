<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExhibitionRequest extends FormRequest
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
            'name' => ['required'],
            'description' => ['required', 'max:255'],
            'product_image' => ['required', 'image', 'mimes:jpeg,png'],
            'product_category_ids' => ['required', 'array'],
            'product_condition_id' => ['required'],
            'price' => ['required', 'integer', 'min:0']
        ];
    }

    public function messages()
    {
        return [
            'name.required' => '商品名を入力してください',
            'description.required' => '商品説明を入力してください',
            'description.max' => '商品説明は255文字以下で入力してください',
            'product_image.required' => '商品画像をアップロードしてください',
            'product_image.mimes' => '商品画像の拡張子は.jpegもしくは.pngでアップロードしてください',
            'product_category_ids.required' => '商品のカテゴリーを選択してください',
            'product_condition_id.required' => '商品の状態を選択してください',
            'price.required' => '商品価格を入力してください',
            'price.integer' => '商品価格は数値型で入力してください',
            'price.min' => '商品価格は0円以上で入力してください',
        ];
    }
}
