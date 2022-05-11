<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Traits\ApiResponser;
use Illuminate\Support\Facades\Response;
use Illuminate\Foundation\Http\FormRequest;

class ProfileRequest extends FormRequest
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
            'first_name' => 'required|string|max:191',
            'last_name' => 'required|string|max:191',
            'profile_image' => 'nullable|image',
            // 'phone' => 'required|numeric|digits_between:11,13|unique:users,phone,'.auth()->user()->id,
            'email' => 'required|email',
            'city' => 'required|string|max:191',
            // 'password' => [
            //     'nullable',
            //     'min:8',
            //     'confirmed'
            // ],
        ];

        // if (auth()->user()->hasRole('supplier')) {
            $rules['gender'] = 'required|string|in:male,female' ;
            $rules['date_of_birth'] = 'required|string' ;
        // }
        
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
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        $valid_errors=$validator->getMessageBag()->toArray();
        $errors=[];

        $car_fields=new User;
        $fields=$car_fields->getFillable();
        foreach ($fields as $key) 
        {
            $message="";
            if(isset($valid_errors[$key]))
            {
                $message= implode("|",$valid_errors[$key]);
            }
            $errors[] = ['key' => $key,'message' => $message];
        }
        throw new \Illuminate\Validation\ValidationException($validator, $this->validationError('Validation error',$errors,400 ));
    }
}
