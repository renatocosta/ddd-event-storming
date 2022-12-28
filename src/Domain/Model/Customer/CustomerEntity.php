<?php

namespace Domain\Model\Customer;

use Common\ValueObjects\AggregateRoot;
use Common\ValueObjects\Geography\Country;
use Common\ValueObjects\Misc\Email;
use Common\ValueObjects\Misc\Mobile;
use Common\ValueObjects\Person\Name;
use Domain\Model\Customer\Events\CustomerCreated;
use Domain\Model\Customer\Events\CustomerOrderLocationAdded;
use Domain\Model\Customer\Events\CustomerOrderPaymentAdded;
use Domain\Model\Customer\Events\CustomerUpdated;

final class CustomerEntity extends AggregateRoot implements Customer
{

    private int $id;

    private Name $name;

    private Mobile $mobile;

    private Email $email;

    private Country $country;

    private CustomerOrderLocation $customerOrderLocation;

    private CustomerOrderPayment $customerOrderPayment;

    public function getId(): int
    {
        return $this->id;
    }

    public function create(): void
    {
        $this->raise(new CustomerCreated($this));
    }

    public function update(): void
    {
        $this->raise(new CustomerUpdated($this));
    }

    public function name(): Name
    {
        return $this->name;
    }

    public function mobile(): Mobile
    {
        return $this->mobile;
    }

    public function email(): Email
    {
        return $this->email;
    }

    public function country(): Country
    {
        return $this->country;
    }

    public function addOrderLocation(CustomerOrderLocation $customerOrderLocation): void
    {
        $this->customerOrderLocation = $customerOrderLocation;
        $this->raise(new CustomerOrderLocationAdded($this));
    }

    public function orderLocation(): CustomerOrderLocation
    {
        return $this->customerOrderLocation;
    }

    public function addOrderPayment(CustomerOrderPayment $customerOrderPayment): void
    {
        $this->customerOrderPayment = $customerOrderPayment;
        $this->raise(new CustomerOrderPaymentAdded($this));
    }

    public function orderPayment(): CustomerOrderPayment
    {
        return $this->customerOrderPayment;
    }

    public function fromExisting(int $id, Name $name, Mobile $mobile, Email $email, Country $country): self
    {
        $this->id = $id;
        $this->name = $name;
        $this->mobile = $mobile;
        $this->email = $email;
        $this->country = $country;

        return $this;
    }

    public function from(Name $name, Mobile $mobile, Email $email, Country $country): self
    {
        $this->name = $name;
        $this->mobile = $mobile;
        $this->email = $email;
        $this->country = $country;

        return $this;
    }
}
