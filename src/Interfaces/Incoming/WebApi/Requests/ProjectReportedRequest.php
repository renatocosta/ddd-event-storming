<?php

namespace Interfaces\Incoming\WebApi\Requests;

use Domain\Model\Order\UnableToHandleOrders;
use Domain\Model\ProjectReports\ProjectReportsStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

final class ProjectReportedRequest extends FormRequest
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
            'event_type' => 'required|in:ProjectReported',
            'entity_type' => 'required|in:Project',
            'version' => 'required|in:1',
            'event_data.request' => 'array|required',
            'event_data.request.name' => 'required',
            'event_data.request.report_text' => 'nullable',
            'event_data.request.channel' => 'required|in:email,sms',
            'event_data.request.email' => 'required_if:event_data.request.channel,email|email',
            'event_data.request.phone_number' => 'required_if:event_data.request.channel,sms|numeric'
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw UnableToHandleOrders::dueTo($validator->errors());
    }

    protected function getValidatorInstance()
    {
        $data = $this->all();

        $data = [
            'event_type' => ProjectReportsStatus::PROJECT_REPORTED,
            'entity_type' => 'Project',
            'version' => 1,
            'event_data' =>
            [
                'request' => $data,
            ]
        ];

        $this->getInputSource()->replace($data);

        return parent::getValidatorInstance();
    }
}
