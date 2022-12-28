<?php

namespace Interfaces\Incoming\WebApi\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class SendTipRequest extends FormRequest
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
            'project_id' => 'required|integer',
            'currency' => 'required',
            'amount' => 'required|regex:/^\d{1,13}(\.\d{1,4})?$/'
        ];
    }
    
}
