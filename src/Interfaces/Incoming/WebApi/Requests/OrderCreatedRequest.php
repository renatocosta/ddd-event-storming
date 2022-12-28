<?php

namespace Interfaces\Incoming\WebApi\Requests;

use Domain\Model\Order\UnableToHandleOrders;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Ddd\BnbSchemaRegistry\SchemaReader;
use Ddd\BnbSchemaRegistry\Schemas;

final class OrderCreatedRequest extends FormRequest
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
        return (new SchemaReader(Schemas::ORDER_CREATED_V1))->load();
    }

    protected function failedValidation(Validator $validator)
    {
        throw UnableToHandleOrders::dueTo($validator->errors());
    }

    protected function getValidatorInstance()
    {
        $dataIncoming = $this->all();

        $data = [
            'event_type' => 'OrderCreated',
            'entity_type' => 'Order',
            'version' => 1,
            'event_data' => $dataIncoming
        ];

        if (!$this->has('customer.location.timezone') || !$this->filled('customer.location.timezone')) {
            $data['event_data']['customer']['location']['timezone'] = 'UTC';
        }

        $this->getInputSource()->replace($data);

        return parent::getValidatorInstance();
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'event_data.cleaners' => 'Cleaners',
            'event_data.cleaners.*.id' => 'Cleaner',
            'event_data.customer' => 'Customer',
            'event_data.customer.payment' => 'Payment',
            'event_data.customer.payment.payment_method_token' => 'Payment Method Token',
            'event_data.customer.payment.card_number_last4' => 'Card Number Last 4 Digits',
            'event_data.customer.payment.customer_token' => 'Customer Token',
            'event_data.customer.property' => 'Property',
            'event_data.customer.property.address' => 'Address',
            'event_data.customer.property.zipcode' => 'Zip Code',
            'event_data.customer.property.extra_details' => 'Extra Details',
            'event_data.customer.property.number_of_bedrooms' => 'Number Of Bedrooms',
            'event_data.customer.property.number_of_bathrooms' => 'Number Of Bathrooms',
            'event_data.customer.property.size' => 'Size',
            'event_data.customer.property.location_coordinates' => 'Coordinates',
            'event_data.customer.property.location_coordinates.lat' => 'Latitude',
            'event_data.customer.property.location_coordinates.long' => 'Longitude',
            'event_data.customer.personal_information' => 'Personal Information',
            'event_data.customer.personal_information.name' => 'Name',
            'event_data.customer.personal_information.phone_number' => 'Phone Number',
            'event_data.customer.personal_information.email' => 'Email',
            'event_data.customer.personal_information.country_code' => 'Country Code',
            'event_data.customer.location' => 'Location',
            'event_data.customer.location.timezone' => 'Timezone',
            'event_data.project.start_date' => 'Start Date'
        ];
    }
}
