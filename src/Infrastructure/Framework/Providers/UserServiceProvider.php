<?php

namespace Infrastructure\Framework\Providers;

use Application\EventHandlers\User\AssignCustomerEventHandler;
use Application\EventHandlers\User\EnrollCustomerAndPaymentEventHandler;
use Application\EventHandlers\User\PaymentMethodUpdatedEventHandler;
use Common\Application\Event\Bus\DomainEventBus;
use Application\EventHandlers\User\UserAuthenticatedEventHandler;
use Application\EventHandlers\User\UserIdentifiedEventHandler;
use Application\EventHandlers\User\UserUpdatedEventHandler;
use Application\EventHandlers\User\UserVerificationCodeRequestedEventHandler;
use Application\EventHandlers\User\UserWithOrderNumberAuthenticatedEventHandler;
use Application\UseCases\Notification\Sms\INotificationSmsPublishUseCase;
use Application\UseCases\User\AssignCustomer\AssignCustomerUseCase;
use Application\UseCases\User\AssignCustomer\IAssignCustomerUseCase;
use Application\UseCases\User\Authenticate\AuthenticateUserUseCase;
use Application\UseCases\User\Authenticate\IAuthenticateUserUseCase;
use Application\UseCases\User\AuthenticateWithOrderNumber\AuthenticateWithOrderNumberUserUseCase;
use Application\UseCases\User\AuthenticateWithOrderNumber\IAuthenticateWithOrderNumberUserUseCase;
use Application\UseCases\User\Identify\IdentifyUserUseCase;
use Application\UseCases\User\Identify\IIdentifyUserUseCase;
use Application\UseCases\User\PaymentMethod\IPaymentMethodUseCase;
use Application\UseCases\User\PaymentMethod\PaymentMethodUseCase;
use Application\UseCases\User\Queries\AuthUserQuery;
use Application\UseCases\User\Queries\GetUserCustomerQuery;
use Application\UseCases\User\Queries\IAuthUserQuery;
use Application\UseCases\User\Queries\IGetUserCustomerQuery;
use Application\UseCases\User\RequestCode\IRequestCodeUserUseCase;
use Application\UseCases\User\RequestCode\RequestCodeUserUseCase;
use Application\UseCases\User\Update\IUpdateUserUseCase;
use Application\UseCases\User\Update\UpdateUserUseCase;
use Common\ValueObjects\Identity\SmsVerificationCode;
use Domain\Model\Order\IOrderRepository;
use Domain\Model\Order\Order;
use Domain\Model\User\IUserRepository;
use Domain\Model\User\User;
use Domain\Model\User\UserEntity;
use Illuminate\Support\ServiceProvider;
use Infrastructure\Repositories\UserRepository;
use Infrastructure\Framework\Entities\UserModel;
use Infrastructure\Proxy\ProxyedBnB;
use Stripe\BaseStripeClient;

class UserServiceProvider extends ServiceProvider
{

    public const DEFAULT_USER_DEPENDENCIES = [DomainEventBus::class => DomainEventBus::class, User::class => User::class, IUserRepository::class => IUserRepository::class, IOrderRepository::class => IOrderRepository::class, SmsVerificationCode::class => SmsVerificationCode::class, Order::class => Order::class, BaseStripeClient::class => BaseStripeClient::class, UserEntity::class => UserEntity::class, UserModel::class => UserModel::class, ProxyedBnB::class => ProxyedBnB::class];

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->loadDependencies();
        $this->loadUseCases();
    }

    public function loadDependencies(): void
    {

        $this->app->bind(IUserRepository::class, function ($app) {
            $userModelWriter = new UserModel();
            $userModelWriter->on('mysql::write');
            $userModelReader = new UserModel();
            $userModelReader->on('mysql::read');
            return new UserRepository($userModelWriter, $userModelReader);
        });

        $this->app->bind(UserIdentifiedEventHandler::class, function ($app, $dependencies) {
            return new UserIdentifiedEventHandler($dependencies[IUserRepository::class] ?? $app[IUserRepository::class]);
        });

        $this->app->bind(UserAuthenticatedEventHandler::class, function ($app, $dependencies) {
            return new UserAuthenticatedEventHandler($dependencies[IUserRepository::class], $app[SmsVerificationCode::class]);
        });

        $this->app->bind(UserWithOrderNumberAuthenticatedEventHandler::class, function ($app) {
            return new UserWithOrderNumberAuthenticatedEventHandler();
        });

        $this->app->bind(UserUpdatedEventHandler::class, function ($app) {
            return new UserUpdatedEventHandler($app[IUserRepository::class]);
        });

        $this->app->bind(AssignCustomerEventHandler::class, function ($app, $dependencies) {
            return new AssignCustomerEventHandler($dependencies[IUserRepository::class]);
        });

        $this->app->bind(User::class, function ($app, $dependencies) {
            return new UserEntity($dependencies[DomainEventBus::class]);
        });

        $this->app->bind(SmsVerificationCode::class, function ($app) {
            return new SmsVerificationCode();
        });

        $this->app->bind(PaymentMethodUpdatedEventHandler::class, function ($app, $dependencies) {
            return new PaymentMethodUpdatedEventHandler($dependencies[ProxyedBnB::class]);
        });

        $this->app->bind(EnrollCustomerAndPaymentEventHandler::class, function ($app, $dependencies) {
            return new EnrollCustomerAndPaymentEventHandler($dependencies[Order::class], $dependencies[IOrderRepository::class], $dependencies[BaseStripeClient::class]);
        });
    }

    public function loadUseCases(): void
    {
        ## Identify an User ##
        $this->app->bind(
            IIdentifyUserUseCase::class,
            function ($app, $dependencies) {

                $dependenciesLoader = defineDependenciesWith($app, $dependencies, self::DEFAULT_USER_DEPENDENCIES);

                $domainEventBus = $dependenciesLoader[DomainEventBus::class];
                $domainEventBus->subscribe($app->makeWith(UserIdentifiedEventHandler::class, $dependenciesLoader));

                if (!key_exists('skip_additional_handlers', $dependencies)) {
                    $domainEventBus->subscribe($app->makeWith(EnrollCustomerAndPaymentEventHandler::class, $dependenciesLoader));
                }

                return new IdentifyUserUseCase($dependenciesLoader[User::class], $dependenciesLoader[SmsVerificationCode::class]);
            }
        );

        ## Authenticate an User ##
        $this->app->bind(
            IAuthenticateUserUseCase::class,
            function ($app, $dependencies) {

                $dependenciesLoader = defineDependenciesWith($app, $dependencies, self::DEFAULT_USER_DEPENDENCIES);

                $dependenciesLoader[DomainEventBus::class]->subscribe(
                    $app->makeWith(UserAuthenticatedEventHandler::class, [IUserRepository::class => $dependenciesLoader[IUserRepository::class]]),
                );
                return new AuthenticateUserUseCase($dependenciesLoader[User::class], $dependenciesLoader[IUserRepository::class]);
            }
        );

        ## Authenticate an User with order number and mobile/email ##
        $this->app->bind(
            IAuthenticateWithOrderNumberUserUseCase::class,
            function ($app) {
                $domainEventBus = $app[DomainEventBus::class];
                $domainEventBus->subscribe($app[UserWithOrderNumberAuthenticatedEventHandler::class]);
                return new AuthenticateWithOrderNumberUserUseCase(new UserEntity($domainEventBus), $app[IUserRepository::class]);
            }
        );

        ## Update user information ##
        $this->app->bind(
            IUpdateUserUseCase::class,
            function ($app) {
                $domainEventBus = $app[DomainEventBus::class];
                $domainEventBus->subscribers($app[UserUpdatedEventHandler::class]);
                return new UpdateUserUseCase(new UserEntity($domainEventBus), $app[IUserRepository::class]);
            }
        );

        ## Resend a SMS verification code ##
        $this->app->bind(
            IRequestCodeUserUseCase::class,
            function ($app) {
                $smsVerificationCode = $app[SmsVerificationCode::class];
                $domainEventBus = new DomainEventBus;
                $userVerificationCodeRequestedEventHandler = new UserVerificationCodeRequestedEventHandler($app->makeWith(IIdentifyUserUseCase::class, ['skip_additional_handlers' => IRequestCodeUserUseCase::class, SmsVerificationCode::class => $smsVerificationCode]), $app[INotificationSmsPublishUseCase::class]);
                $domainEventBus->subscribers($userVerificationCodeRequestedEventHandler, $app[UserIdentifiedEventHandler::class]);
                return new RequestCodeUserUseCase(new UserEntity($domainEventBus), $app[IUserRepository::class], $smsVerificationCode);
            }
        );

        ## Assing a Customer ##
        $this->app->bind(
            IAssignCustomerUseCase::class,
            function ($app, $dependencies) {

                $dependenciesLoader = defineDependenciesWith($app, $dependencies, self::DEFAULT_USER_DEPENDENCIES);

                $dependenciesLoader[DomainEventBus::class]->subscribers(
                    $app->makeWith(AssignCustomerEventHandler::class, [IUserRepository::class => $dependenciesLoader[IUserRepository::class]])
                );

                return new AssignCustomerUseCase($dependenciesLoader[User::class], $dependenciesLoader[IUserRepository::class]);
            }
        );

        ## Update Payment Method ##
        $this->app->bind(
            IPaymentMethodUseCase::class,
            function ($app) {
                $proxyBnb = $app[ProxyedBnB::class];
                $domainEventBus = $app[DomainEventBus::class];
                $domainEventBus->subscribe($app->makeWith(PaymentMethodUpdatedEventHandler::class, [ProxyedBnB::class => $proxyBnb]));
                return new PaymentMethodUseCase(new UserEntity($domainEventBus), $app[IUserRepository::class], $proxyBnb);
            }
        );

        ## Authentication by email/password provided ##
        $this->app->bind(
            IAuthUserQuery::class,
            function ($app) {
                return new AuthUserQuery($app[IUserRepository::class]);
            }
        );

        ## Get user by customer id ##
        $this->app->bind(
            IGetUserCustomerQuery::class,
            function ($app, $dependencies) {
                $dependenciesLoader = defineDependenciesWith($app, $dependencies, self::DEFAULT_USER_DEPENDENCIES);
                return new GetUserCustomerQuery($dependenciesLoader[IUserRepository::class]);
            }
        );
    }
}
