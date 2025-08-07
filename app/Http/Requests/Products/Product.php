<?php

namespace App\Http\Requests\Products;

use Illuminate\Foundation\Http\FormRequest;

class Product extends FormRequest
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
     * @return array
     */
    public function rules()
    {
        return [
            'name'            => ['required'],
            'price'           => ['required'],
            'presentation_id' => ['required'],
            'company_id'      => ['required|integer'],
            'product_metadata_code' => ['required', 'exists:product_metadata,code'],
        ];
    }

    public function messages()
    {
        return [
            'name.required'            => 'Debe ingresar un nombre al producto',
            'price.required'           => 'Ingrese precio del producto',
            'presentation_id.required' => 'Debe seleccionar una presentacion del producto',
            'company_id.required'      => 'Debe loguearse seleccionando una empresa',
            'product_metadata_code.required'  => 'Debe seleccionar una categoria para este medicamento'
        ] ;
    }
}
