<?php

namespace Application\Orchestration;

use Application\UseCases\Customer\Create\CreateCustomerInput;
use Application\UseCases\Customer\Create\ICreateCustomerUseCase;
use Application\UseCases\Customer\CustomerAddOrderLocation\AddOrderLocationInput;
use Application\UseCases\Customer\CustomerAddOrderLocation\AddOrderLocationUseCase;
use Application\UseCases\Customer\CustomerAddOrderLocation\IAddOrderLocationUseCase;
use Application\UseCases\Customer\CustomerAddOrderPayment\AddOrderPaymentInput;
use Application\UseCases\Customer\CustomerAddOrderPayment\AddOrderPaymentUseCase;
use Application\UseCases\Customer\CustomerAddOrderPayment\IAddOrderPaymentUseCase;
use Application\UseCases\Notification\Sms\INotificationSmsPublishUseCase;
use Application\UseCases\Notification\Sms\NotificationSmsInput;
use Application\UseCases\Order\Create\CreateOrderInput;
use Application\UseCases\Order\Create\CreateOrderUseCase;
use Application\UseCases\Order\Create\ICreateOrderUseCase;
use Application\UseCases\Property\Create\CreatePropertyInput;
use Application\UseCases\Property\Create\ICreatePropertyUseCase;
use Application\UseCases\User\Identify\IdentifyUserInput;
use Application\UseCases\User\Identify\IdentifyUserUseCase;
use Application\UseCases\User\Identify\IIdentifyUserUseCase;
use Common\Application\Orchestration\UseCasesOrchestrator;
use Domain\Model\Customer\Customer;
use Domain\Model\Notification\NotificationMessage;
use Illuminate\Database\DatabaseManager as DB;
use Domain\Model\Order\Order;
use Domain\Model\User\User;
use Generator;

final class CreateOrderOrchestrator extends UseCasesOrchestrator
{

    public function __construct(
        private DB $db,
        private ICreateCustomerUseCase $createCustomerUseCase,
        private ICreatePropertyUseCase $createPropertyUseCase,
        private ICreateOrderUseCase $createOrderUseCase,
        private IAddOrderLocationUseCase $addOrderLocationUseCase,
        private IIdentifyUserUseCase $identifyUserUseCase,
        private IAddOrderPaymentUseCase $addOrderPaymentUseCase,
        private INotificationSmsPublishUseCase $notificationSmsPublishUseCase,
        public Order $order,
        private User $user,
        private Customer $customer
    ) {
    }

    protected function loadUseCases($initialInput): Generator
    {
        $customer = $initialInput['event_data']['customer'];
        $customerPerson = $customer['personal_information'];
        $customerLocation = $customer['location'];
        $customerProperty = $customer['property'];
        $customerPayment = $customer['payment'];

        yield $this->createCustomerUseCase => new CreateCustomerInput($customerPerson['name'], $customerPerson['phone_number'], $customerPerson['email'], $customerPerson['country_code']);
        yield $this->createPropertyUseCase => new CreatePropertyInput($this->customer->getId(), $customerProperty['address'], $customerProperty['zipcode'], $customerProperty['state'], $customerProperty['number_of_bedrooms'], $customerProperty['city'], $customerProperty['extra_details'], $customerProperty['number_of_bathrooms'], $customerProperty['size'], $customerProperty['location_coordinates']['lat'], $customerProperty['location_coordinates']['long']);
        yield $this->createOrderUseCase => new CreateOrderInput($initialInput, $this->customer->getId(), $this->createPropertyUseCase->property->getId());
        yield $this->addOrderLocationUseCase => new AddOrderLocationInput($customerLocation['timezone'], $this->order->getIdentifier()->id, $this->customer->getId());
        yield $this->identifyUserUseCase => new IdentifyUserInput($this->statements['name'], $this->statements['email'], $this->statements['phone_number'], $this->order->getIdentifier()->id, $this->customer->getId());
        yield $this->addOrderPaymentUseCase => new AddOrderPaymentInput($this->order->getIdentifier()->id, $customerPayment['payment_method_token'], $customerPayment['card_number_last4'], $this->order->payload()->asArray()['event_data']['customer']['payment']['customer_token']);
        yield $this->notificationSmsPublishUseCase => new NotificationSmsInput($this->user->mobile(), $this->statements);
    }

    public function execute($initialInput): void
    {
        $this->db->transaction(function () use ($initialInput) {
            parent::execute($initialInput);
        });
    }

    protected function returnNextStatementFrom($useCase): array
    {

        switch ($useCase) {

            case $useCase instanceof CreateOrderUseCase:
                $this->order->payload()->addAList([
                    'event_data' =>
                    [
                        'customer' =>
                        [
                            'id' => $this->customer->getId()
                        ]
                    ]
                ]);
                $payload = $this->order->payload()->asArray();
                return $payload['event_data']['customer']['personal_information'];

            case $useCase instanceof IdentifyUserUseCase:
                $this->order->payload()->addAList([
                    'event_data' =>
                    [
                        'customer' =>
                        [
                            'user_id' => $this->user->getIdentifier()->id
                        ]
                    ]
                ]);

                return ['text' => sprintf(NotificationMessage::PHONE_NUMBER_VERIFICATION, $this->user->password()->asRaw())];

            case $useCase instanceof AddOrderLocationUseCase || $useCase instanceof AddOrderPaymentUseCase:
                return $this->statements;

            default:
                return [];
        }
    }
}
