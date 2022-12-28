<?php

namespace Tests\User;

use DG\BypassFinals;
use Domain\Model\User\UnableToHandleUser;
use Domain\Model\User\UserAbilities;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Utilities\TestCase;

class UserAbilitiesTest extends TestCase
{

    use WithFaker;

    public static function setUpBeforeClass(): void
    {
        BypassFinals::enable();
    }

    public function test_should_fail_to_invalid_ability(): void
    {
        $this->expectException(UnableToHandleUser::class);
        new UserAbilities('adlkdd', 'bcbcmnbc', 'cccdddd');
    }

    public function test_entering_a_valid_ability(): void
    {
        $userAbilities = new UserAbilities(UserAbilities::MANAGE_GLOBAL, UserAbilities::MANAGE_ORDER, UserAbilities::MANAGE_PROJECT, UserAbilities::MANAGE_USER, UserAbilities::MANAGE_REVIEW, UserAbilities::SHARE_REPORT, UserAbilities::MANAGE_PAYMENT);
        $this->assertEquals($userAbilities->abilities(), UserAbilities::ABILITIES_AVAILABLE);
    }
}
