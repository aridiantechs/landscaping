<?php

namespace App\Http\Requests;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\RequestMedia;
use App\Traits\ApiResponser;
use App\Models\RequestCategory;
use Illuminate\Support\Facades\Response;
use Illuminate\Foundation\Http\FormRequest;

class OrderRequest extends FormRequest
{
    use ApiResponser;
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
        $rules = [
            // 'uuid' => ['required', 'numeric', 'exists:addresses,id,user_id,'.auth()->user()->id],
            'city' => ['required', 'string'],
            'state' => ['required', 'string'],
            'country' => ['required', 'string'],
            'lat' => ['required', 'string'],
            'lng' => ['required', 'string'],
            'full_address' => ['required', 'string'],
        ];

        $requestWise = [];

        switch($this->method())
        {
            case 'GET':
            case 'DELETE':
            {
                break;
            }
            case 'PATCH':
            case 'PUT':
            case 'POST':
            {
                $requestWise = $rules;
                break;
            }
            default:break;
        }

        // rules
        return $requestWise;
    }


    public function attributes()
    {
        return [
            // 
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        $fields1=new Order;
        $fields=$fields1->getFillable();
        $errors=$this->formatErrors($fields,$validator->errors());
        
        throw new \Illuminate\Validation\ValidationException($validator, $this->validationError('Validation error',$errors,400 ));
    }
}
