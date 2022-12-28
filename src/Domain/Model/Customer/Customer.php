<?php

namespace Domain\Model\Customer;

use Common\ValueObjects\Geography\Country;
use Common\ValueObjects\Misc\Email;
use Common\ValueObjects\Misc\Mobile;
use Common\ValueObjects\Person\Name;

interface Customer
{

    public function getId(): int;

    public function create(): void;

    public function update(): void;

    public function name(): Name;

    public function mobile(): Mobile;

    public function email(): Email;

    public function country(): Country;

    public function addOrderLocation(CustomerOrderLocation $customerOrderLocation): void;

    public function orderLocation(): CustomerOrderLocation;

    public function addOrderPayment(CustomerOrderPayment $customerOrderPayment): void;

    public function orderPayment(): CustomerOrderPayment;

    public function fromExisting(int $id, Name $name, Mobile $mobile, Email $email, Country $country): self;

    public function from(Name $name, Mobile $mobile, Email $email, Country $country): self;
}
