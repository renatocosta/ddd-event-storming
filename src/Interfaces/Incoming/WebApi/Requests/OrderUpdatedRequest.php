<?php

namespace Interfaces\Incoming\WebApi\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class OrderUpdatedRequest extends FormRequest
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
            'order_id' => 'required',
            'cleaners' => 'array|required',
            'cleaners.*.id' => 'required|distinct|integer',
            'cleaners.*.name' => 'required'
        ];
    }
}
