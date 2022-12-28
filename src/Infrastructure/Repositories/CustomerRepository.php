<?php

namespace Infrastructure\Repositories;

use Common\ValueObjects\Geography\Country;
use Common\ValueObjects\Misc\Email;
use Common\ValueObjects\Misc\Mobile;
use Common\ValueObjects\Person\Name;
use Domain\Model\Customer\Customer;
use Domain\Model\Customer\CustomerEntity;
use Domain\Model\Customer\ICustomerRepository;
use Infrastructure\Framework\Entities\CustomerOrderLocationsModel;
use Infrastructure\Framework\Entities\CustomerOrderPaymentsModel;
use Infrastructure\Framework\Entities\CustomersModel;

final class CustomerRepository implements ICustomerRepository
{

    public function __construct(
        private CustomersModel $customerModelWriter,
        private CustomersModel $customerModelReader,
        private CustomerOrderPaymentsModel $customerOrderPaymentsModelWriter,
        private CustomerOrderLocationsModel $customerOrderLocationsModelWriter
    ) {
    }

    public function getById(int $id): Customer
    {
        $customer = $this->customerModelReader
            ->findOrFail($id);

        return (new CustomerEntity())->fromExisting($id, new Name($customer->name), new Mobile($customer->mobile), new Email($customer->email), new Country($customer->country_code));
    }

    public function create(object $customer): void
    {
        $customerCreated = $this->customerModelWriter
            ->updateOrCreate(['mobile' => $customer->mobile()], ['name' => $customer->name(), 'mobile' => $customer->mobile(), 'email' => $customer->email(), 'country_code' => $customer->country()]);
        $customer->fromExisting($customerCreated->id, $customer->name(), $customer->mobile(), $customer->email(), $customer->country());
    }

    public function update(object $customer): void
    {
        $customerUpd = $this->customerModelWriter->findOrFail($customer->getId());
        $customerUpd->name = $customer->name();
        $customerUpd->email = $customer->email();
        $customerUpd->mobile = $customer->mobile();
        $customerUpd->country = $customer->country();
        $customerUpd->save();
    }

    public function createWithOrderLocation(object $customer): void
    {
        $orderLocation = $customer->orderLocation();

        $this->customerOrderLocationsModelWriter
            ->create(['timezone' => $orderLocation->timezone(), 'order_id' => $orderLocation->orderId(), 'customer_id' => $customer->getId()]);
    }

    public function createWithOrderPayment(object $customer): void
    {
        $orderPayment = $customer->orderPayment();
        $this->customerOrderPaymentsModelWriter
            ->create(['order_id' => $orderPayment->orderId(), 'customer_id' => $customer->getId(), 'payment_method_token' => $orderPayment->paymentMethodToken(), 'card_number_last4' => $orderPayment->cardNumberLast4(), 'customer_token' => $orderPayment->customerToken()]);
    }
}
