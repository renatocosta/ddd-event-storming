<?php

namespace Infrastructure\Repositories;

use Common\ValueObjects\Identity\Guid;
use Common\ValueObjects\Identity\Identified;
use Common\ValueObjects\Misc\Email;
use Common\ValueObjects\Misc\Mobile;
use Domain\Model\User\IUserRepository;
use Domain\Model\User\User;
use Domain\Model\User\UserEntity;
use Domain\Model\User\UserEntityNotFound;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Infrastructure\Framework\Entities\UserModel;
use Illuminate\Support\Facades\Hash;

final class UserRepository implements IUserRepository
{

    public const PAGINATE_ITEMS_PER_PAGE = 10;

    public function __construct(private UserModel $userModelWriter, private UserModel $userModelReader)
    {
    }

    public function get(string $mobile): object|null
    {
        return $this->userModelReader->where('mobile', $mobile)->first();
    }

    public function getAll(array $filter = ['id', 'name', 'email', 'customer_id', 'mobile', 'created_at', 'updated_at']): array
    {
        return $this->userModelReader
            ->select($filter)
            ->orderBy('created_at', 'DESC')
            ->simplePaginate(self::PAGINATE_ITEMS_PER_PAGE)->toArray();
    }

    public function getByEmailAndPassword(string $email, string $password): UserModel
    {
        $user = $this->userModelReader->where('email', $email)->first();
        if (!$user || !$user->isAdmin() || !Hash::check($password, $user->password)) throw new ModelNotFoundException();

        return $user;
    }

    public function getById(Identified $identifier): UserModel
    {
        return $this->userModelReader
            ->with(['order' => function ($query) {
            }])
            ->findOrFail($identifier->getId());
    }

    public function getWithOrderAndMobile(string $orderNumber, string $mobile): ?object
    {
        return $this->userModelReader->where('mobile', $mobile)
            ->with(['order' => function ($query) use ($orderNumber) {
                $query->where('order_number', $orderNumber);
            }])
            ->first();
    }

    public function getWithOrderAndEmail(string $orderNumber, string $email): object|null
    {
        return $this->userModelReader->where('email', $email)
            ->with(['customer.order' => function ($query) use ($orderNumber) {
                $query->where('order_number', $orderNumber);
            }])
            ->first();
    }

    public function getWithOrder(string $orderId): object|null
    {
        return $this->userModelReader
            ->whereHas('customer.order', function ($query) use ($orderId) {
                $query->where('id', $orderId);
            })->firstOrFail();
    }

    public function getWithOrderNumber(string $orderNumber): object|null
    {
        return $this->userModelReader
            ->select('id', 'mobile')
            ->with(['order' => function ($query) use ($orderNumber) {
                $query->select('order_number', 'user_id');
                $query->where('order_number', $orderNumber);
            }])->whereHas('order', function ($query) use ($orderNumber) {
                $query->select('order_number', 'user_id');
                $query->where('order_number', $orderNumber);
            })
            ->first();
    }

    public function getByCustomer(int $customerId): User
    {
        $existingUser = $this->userModelReader
            ->where('customer_id', $customerId)
            ->firstOrFail();

        return (new UserEntity())->withCustomerId(Guid::from($existingUser->id), $existingUser->name, new Mobile($existingUser->mobile), new Email($existingUser->email), '', $existingUser->customer_id);
    }

    public function getByCustomerAndUserId(int $customerId, string $userId): User
    {
        $user = $this->userModelReader
            ->where('customer_id', $customerId)
            ->find($userId);

        if ($user) {
            return (new UserEntity())->withCustomerId(Guid::from($user->id), $user->name, new Mobile($user->mobile), new Email($user->email), '', $customerId);
        }

        return new UserEntityNotFound();
    }

    public function create(object $user): UserModel
    {

        $identifier = $user->getIdentifier()->id;
        $existingUser = $this->userModelWriter->where('mobile', $user->mobile())->first();
        if ($existingUser != null) {
            $existingUser->update(['name' => $user->name(), 'email' => (string) $user->email(), 'password' => (string) $user->password()]);
            return $existingUser;
        }

        $newUser = $this->userModelWriter->create(
            ['id' => $identifier, 'customer_id' => $user->getCustomerId(), 'name' => $user->name(), 'email' => (string) $user->email(), 'mobile' => $user->mobile(),  'password' => (string) $user->password()]
        );

        return $newUser;
    }

    public function update(object $user): void
    {
        $userUpd = $this->userModelWriter->find($user->getIdentifier()->id);
        $userUpd->name = $user->name();
        $userUpd->email = $user->email();
        $userUpd->mobile = $user->mobile();

        if ($user->getCustomerId() > 0) {
            $userUpd->customer_id = $user->getCustomerId();
        }

        $userUpd->save();
    }
}
