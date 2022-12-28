<?php

namespace Domain\Model\Customer;

use Common\ValueObjects\Geography\Country;
use Common\ValueObjects\Misc\Email;
use Common\ValueObjects\Misc\Mobile;
use Common\ValueObjects\Person\Name;

final class CustomerEntityNotFound implements Customer
{

    private CustomerOrderLocation $customerOrderLocation;

    private CustomerOrderPayment $customerOrderPayment;

    private Name $name;

    private Email $email;

    private Mobile $mobile;

    private Country $country;

    public function getId(): int
    {
        return 0;
    }

    public function create(): void
    {
    }

    public function update(): void
    {
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
    }

    public function orderLocation(): CustomerOrderLocation
    {
        return $this->customerOrderLocation;
    }

    public function addOrderPayment(CustomerOrderPayment $customerOrderPayment): void
    {
        $this->customerOrderPayment = $customerOrderPayment;
    }

    public function orderPayment(): CustomerOrderPayment
    {
        return $this->customerOrderPayment;
    }

    public function fromExisting(int $id, Name $name, Mobile $mobile, Email $email, Country $country): self
    {
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
