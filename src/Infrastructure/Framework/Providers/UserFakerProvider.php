<?php

namespace Infrastructure\Framework\Providers;

use Application\UseCases\User\AssignCustomer\AssignCustomerUseCase;
use Application\UseCases\User\AssignCustomer\IAssignCustomerUseCase;
use Application\UseCases\User\Authenticate\AuthenticateUserUseCase;
use Application\UseCases\User\Authenticate\IAuthenticateUserUseCase;
use Application\UseCases\User\Identify\IdentifyUserUseCase;
use Application\UseCases\User\Identify\IIdentifyUserUseCase;
use Common\Application\Event\Bus\DomainEventBus;
use Common\ValueObjects\Identity\SmsVerificationCode;
use Common\ValueObjects\Misc\HumanCode;
use Domain\Model\User\User;
use Faker\Provider\Base;
use Illuminate\Support\Facades\Hash;
use Infrastructure\Framework\Entities\CustomersModel;
use Infrastructure\Repositories\UserRepositoryFake;
use Infrastructure\Framework\Entities\OrderModel;
use Infrastructure\Framework\Entities\UserModel;
use Mockery;
use Ramsey\Uuid\Uuid;

class UserFakerProvider extends Base
{

    public function authenticateUserCase($app): IAuthenticateUserUseCase
    {
        return new AuthenticateUserUseCase($app[User::class], new UserRepositoryFake);
    }

    public function identifyUserUseCase($app, $domainEventBus): IIdentifyUserUseCase
    {
        return new IdentifyUserUseCase($app->makeWith(User::class, [DomainEventBus::class => $domainEventBus]), new SmsVerificationCode());
    }

    public function assignCustomerUseCase($app, $domainEventBus): IAssignCustomerUseCase
    {
        return new AssignCustomerUseCase($app->makeWith(User::class, [DomainEventBus::class => $domainEventBus]), new UserRepositoryFake);
    }

    public function userModel(int $smsVerificationCode = 123456)
    {
        $userModel = Mockery::mock(new UserModel());
        $userModel->id = Uuid::uuid4();
        $userModel->customer_id = 2355;
        $userModel->name = $this->generator->name();
        $userModel->password = Hash::make($smsVerificationCode);
        $userModel->email = $this->generator->email();
        $userModel->mobile = $this->generator->phoneNumber;
        $userModel->shouldReceive('save');
        $personalAccessToken = new \Laravel\Sanctum\PersonalAccessToken();
        $userModel->shouldReceive('createToken')->andReturn(new \Laravel\Sanctum\NewAccessToken($personalAccessToken, 'slskslk22'));
        $userModel->shouldReceive('tokens->delete');
        $userModel->customer = Mockery::spy(new CustomersModel());
        $userModel->customer->order = Mockery::spy(new OrderModel());
        $userModel->customer->order->id = Uuid::uuid4();
        $userModel->customer->order->order_number = new HumanCode();

        return $userModel;
    }
}
