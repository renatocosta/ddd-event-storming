<?php

namespace Tests\Common;

use Assert\Assert;
use Assert\AssertionFailedException;
use Common\Exception\UnableToHandleBusinessRules;
use Common\ValueObjects\DateTime\PreferredTime;
use Common\ValueObjects\Geography\Address;
use Common\ValueObjects\Geography\City;
use Common\ValueObjects\Geography\Country;
use Common\ValueObjects\Geography\State;
use Common\ValueObjects\Geography\Timezone;
use Common\ValueObjects\Geography\Zipcode;
use Common\ValueObjects\Identity\Password;
use Common\ValueObjects\Identity\SmsVerificationCode;
use Common\ValueObjects\Misc\Bathrooms;
use Common\ValueObjects\Misc\Bedrooms;
use Common\ValueObjects\Misc\Coordinates;
use Common\ValueObjects\Misc\Email;
use Common\ValueObjects\Misc\ExtraDetails;
use Common\ValueObjects\Misc\HumanCode;
use Common\ValueObjects\Misc\Payload;
use Common\ValueObjects\Misc\Size;
use Common\ValueObjects\Misc\SmsText;
use Common\ValueObjects\Person\Name;
use DG\BypassFinals;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Utilities\TestCase;

class ValueObjectsTest extends TestCase
{

    use WithFaker;

    public static function setUpBeforeClass(): void
    {
        BypassFinals::enable();
    }

    public function test_should_fail_to_invalid_password(): void
    {
        $this->expectException(UnableToHandleBusinessRules::class);
        new Password($this->faker->randomNumber(3, true), 6);
    }

    public function test_entering_a_valid_password(): void
    {
        new Password($this->faker->randomNumber(8, true), 8);
        self::expectNotToPerformAssertions();
    }

    public function test_should_fail_to_invalid_smsverificationcode(): void
    {
        $this->expectException(UnableToHandleBusinessRules::class);
        new SmsVerificationCode($this->faker->randomNumber(9, true));
    }

    public function test_entering_a_valid_smsverificationcode(): void
    {
        new SmsVerificationCode($this->faker->randomNumber(6, true));
        self::expectNotToPerformAssertions();
    }

    public function test_should_fail_to_invalid_email(): void
    {
        $this->expectException(UnableToHandleBusinessRules::class);
        new Email($this->faker->unique()->word);
    }

    public function test_entering_a_valid_email(): void
    {
        new Email($this->faker->email());
        self::expectNotToPerformAssertions();
    }

    public function test_should_fail_to_invalid_humancode(): void
    {
        $this->expectException(UnableToHandleBusinessRules::class);
        $invalidCodes = $this->faker->randomElement(['ABC12', '1762', '121AD', 'KJHAKJkjkja', 12]);
        new HumanCode($invalidCodes);
    }

    public function test_entering_a_valid_humancode(): void
    {
        $validCode = new HumanCode();
        new HumanCode($validCode);
        new HumanCode();
        self::expectNotToPerformAssertions();
    }

    public function test_should_fail_to_invalid_smstext(): void
    {
        $this->expectException(UnableToHandleBusinessRules::class);
        $largeText = $this->faker->sentence(200);
        new SmsText($largeText);
    }

    public function test_entering_a_valid_smstext(): void
    {
        new SmsText('Your confirmation code is :');
        self::expectNotToPerformAssertions();
    }

    public function test_should_fail_to_invalid_payload(): void
    {
        $this->expectException(UnableToHandleBusinessRules::class);
        $input = $this->faker->word();
        new Payload($input);
    }

    public function test_entering_a_valid_payload(): void
    {
        $payload = $this->faker->payload();
        new Payload($payload);
        self::expectNotToPerformAssertions();
    }

    public function test_should_fail_to_invalid_country(): void
    {
        $this->expectException(UnableToHandleBusinessRules::class);
        $input = $this->faker->realTextBetween(11, 100);
        new Country($input);
    }

    public function test_entering_a_valid_country(): void
    {
        $input = $this->faker->countryCode();
        new Country($input);
        self::expectNotToPerformAssertions();
    }

    public function test_should_fail_to_invalid_timezone(): void
    {
        $this->expectException(UnableToHandleBusinessRules::class);
        $input = $this->faker->realTextBetween(100, 200);
        new Timezone($input);
    }

    public function test_entering_a_valid_timezone(): void
    {
        $input = $this->faker->text(30);
        new Timezone($input);
        self::expectNotToPerformAssertions();
    }

    public function test_should_fail_to_invalid_name(): void
    {
        $this->expectException(UnableToHandleBusinessRules::class);
        $input = $this->faker->realTextBetween(150, 300);
        new Name($input);
    }

    public function test_entering_a_valid_name(): void
    {
        $input = $this->faker->text(30);
        new Name($input);
        self::expectNotToPerformAssertions();
    }

    public function test_should_fail_to_invalid_address(): void
    {
        $this->expectException(UnableToHandleBusinessRules::class);
        $input = $this->faker->realTextBetween(1020, 2000);
        new Address($input);
    }

    public function test_entering_a_valid_address(): void
    {
        $input = $this->faker->text(100);
        new Address($input);
        self::expectNotToPerformAssertions();
    }

    public function test_should_fail_to_invalid_zipcode(): void
    {
        $this->expectException(UnableToHandleBusinessRules::class);
        $input = $this->faker->realTextBetween(200, 300);
        new Zipcode($input);
    }

    public function test_entering_a_valid_zipcode(): void
    {
        $input = $this->faker->text(12);
        new Zipcode($input);
        self::expectNotToPerformAssertions();
    }

    public function test_should_fail_to_invalid_state(): void
    {
        $this->expectException(UnableToHandleBusinessRules::class);
        $input = $this->faker->realTextBetween(40, 100);
        new State($input);
    }

    public function test_entering_a_valid_state(): void
    {
        $input = $this->faker->text(12);
        new State($input);
        self::expectNotToPerformAssertions();
    }

    public function test_should_fail_to_invalid_bedrooms(): void
    {
        $this->expectException(UnableToHandleBusinessRules::class);
        $input = $this->faker->realTextBetween(100, 200);
        new Bedrooms($input);
    }

    public function test_entering_a_valid_bedrooms(): void
    {
        $input = $this->faker->text(29);
        new Bedrooms($input);
        self::expectNotToPerformAssertions();
    }

    public function test_should_fail_to_invalid_city(): void
    {
        $this->expectException(UnableToHandleBusinessRules::class);
        $input = $this->faker->realTextBetween(100, 200);
        new City($input);
    }

    public function test_entering_a_valid_city(): void
    {
        $input = $this->faker->text(29);
        new City($input);
        self::expectNotToPerformAssertions();
    }

    public function test_should_fail_to_invalid_extra_details(): void
    {
        $this->expectException(UnableToHandleBusinessRules::class);
        $input = $this->faker->realTextBetween(1000, 1300);
        new ExtraDetails($input);
    }

    public function test_entering_a_valid_extra_details(): void
    {
        $input = $this->faker->text(130);
        new ExtraDetails($input);
        self::expectNotToPerformAssertions();
    }

    public function test_should_fail_to_invalid_bath_rooms(): void
    {
        $this->expectException(UnableToHandleBusinessRules::class);
        $input = $this->faker->realTextBetween(30, 50);
        new Bathrooms($input);
    }

    public function test_entering_a_valid_bath_rooms(): void
    {
        $input = $this->faker->text(30);
        new Bathrooms($input);
        self::expectNotToPerformAssertions();
    }

    public function test_should_fail_to_invalid_size(): void
    {
        $this->expectException(UnableToHandleBusinessRules::class);
        new Size('199999999.99');
    }

    public function test_entering_a_valid_size(): void
    {
        $input = $this->faker->unique()->randomElement(array(99999999.99, 99999999.01, 99999999.00, 9999999.01, 7777.12));
        new Size($input);
        self::expectNotToPerformAssertions();
    }

    public function test_should_fail_to_invalid_coordinates(): void
    {
        $this->expectException(UnableToHandleBusinessRules::class);
        $lat = $this->faker->unique()->randomElement(array(-91.00, -98.02));
        $lon = $this->faker->unique()->randomElement(array(-120.00, 190.0));
        new Coordinates($lat, $lon);
    }

    public function test_entering_a_valid_coordinates(): void
    {
        new Coordinates($this->faker->latitude(), $this->faker->longitude());
        self::expectNotToPerformAssertions();
    }

    public function test_should_fail_to_invalid_preferred_time(): void
    {
        $this->expectException(UnableToHandleBusinessRules::class);
        new PreferredTime('XYZZJLK');
    }

    /**
     * @testWith ["morning"]
     *           ["afternoon"]
     */
    public function test_entering_a_valid_preferred_time(string $period): void
    {
        new PreferredTime($period);
        self::expectNotToPerformAssertions();
    }
}
