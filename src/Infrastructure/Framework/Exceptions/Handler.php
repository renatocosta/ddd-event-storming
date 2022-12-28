<?php

namespace Infrastructure\Framework\Exceptions;

use Assert\AssertionFailedException;
use Ddd\BnbSchemaRegistry\UnableToHandleBnbSchemaRegistryException;
use Common\Exception\UnableToHandleBusinessRules;
use Domain\Model\Notification\UnableToHandleNotification;
use Domain\Model\Order\UnableToHandleOrders;
use Domain\Model\ProjectReports\UnableToHandleProjectReports;
use Domain\Model\User\UnableToHandleUser;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [];

    private const INTERNAL_SERVER_ERROR_MESSAGE = 'Something went wrong while processing your request';

    private const NOT_FOUND_MESSAGE = 'Could not find what you were looking for.';

    private const DUPLICATED_ENTRY_CODE = '23000';

    private const MSG_EMAIL_DUPLICATED = 'This email is already associated with another phone number';

    private const MSG_MOBILE_DUPLICATED = 'This phone number is already associated with another email';

    private const MSG_ORDER_NUMBER_DUPLICATED = 'This order number already exists. Try again.';

    private const DUPLICATED_ENTRY_KEYS = ['users.users_email_unique' => self::MSG_EMAIL_DUPLICATED, 'users_email_unique' => self::MSG_EMAIL_DUPLICATED, 'user.users_mobile_unique' => self::MSG_MOBILE_DUPLICATED, 'users_mobile_unique' => self::MSG_MOBILE_DUPLICATED, 'orders.order_number_status_unique' => self::MSG_ORDER_NUMBER_DUPLICATED, 'order_number_status_unique' => self::MSG_ORDER_NUMBER_DUPLICATED];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {

        $this->renderable(function (UnableToHandleOrders $e, $request) {
            $response = [
                'description' => $e->getMessage(),
            ];
            return $this->asJson($response, Response::HTTP_UNPROCESSABLE_ENTITY);
        });

        $this->renderable(function (UnableToHandleUser $e, $request) {
            $response = [
                'description' => $e->getMessage(),
            ];
            return $this->asJson($response, Response::HTTP_UNPROCESSABLE_ENTITY);
        });

        $this->renderable(function (UnableToHandleNotification $e, $request) {
            $response = [
                'description' => $e->getMessage(),
            ];
            return $this->asJson($response, Response::HTTP_UNPROCESSABLE_ENTITY);
        });

        $this->renderable(function (UnableToHandleProjectReports $e, $request) {
            $response = [
                'description' => $e->getMessage(),
            ];
            return $this->asJson($response, Response::HTTP_UNPROCESSABLE_ENTITY);
        });

        $this->renderable(function (UnableToHandleBnbSchemaRegistryException $e, $request) {
            $response = [
                'description' => $e->getMessage(),
            ];
            return $this->asJson($response, Response::HTTP_UNPROCESSABLE_ENTITY);
        });

        $this->renderable(function (UnableToHandleBusinessRules $e, $request) {
            $response = [
                'description' => $e->getMessage(),
            ];
            return $this->asJson($response, Response::HTTP_UNPROCESSABLE_ENTITY);
        });

        $this->renderable(function (AssertionFailedException $e, $request) {
            $response = [
                'description' => $e->getMessage(),
            ];
            return $this->asJson($response, Response::HTTP_UNPROCESSABLE_ENTITY);
        });

        $this->renderable(function (ModelNotFoundException $e, $request) {
            $message = app()->environment(['local', 'staging']) ? $e->getMessage() : self::NOT_FOUND_MESSAGE;
            $response = [
                'description' => $message,
            ];
            return $this->asJson($response, Response::HTTP_NOT_FOUND);
        });

        $this->renderable(function (NotFoundHttpException $e, $request) {
            $response = [
                'description' => self::NOT_FOUND_MESSAGE,
            ];
            return $this->asJson($response, Response::HTTP_NOT_FOUND);
        });

        $this->renderable(function (MethodNotAllowedHttpException $e, $request) {
            $response = [
                'description' => $e->getMessage(),
            ];
            return $this->asJson($response, Response::HTTP_METHOD_NOT_ALLOWED);
        });

        $this->renderable(function (AuthenticationException $e, $request) {
            $response = [
                'description' => $e->getMessage(),
            ];
            return $this->asJson($response, Response::HTTP_UNAUTHORIZED);
        });

        $this->renderable(function (QueryException $e, $request) {
            $errorDescription = 'Something went wrong while executing your query';

            if ($e->getCode() == self::DUPLICATED_ENTRY_CODE) {
                $reason = explode('for key ', str_replace("'", "", $e->errorInfo[2]));
                $keyReason = end($reason);

                $duplicatedMessage = isset(self::DUPLICATED_ENTRY_KEYS[$keyReason]) ? self::DUPLICATED_ENTRY_KEYS[$keyReason] : null;

                if (!empty($duplicatedMessage)) {
                    $errorDescription = $duplicatedMessage;
                }
            }
            $response = [
                'description' => $errorDescription,
            ];
            return $this->asJson($response, Response::HTTP_UNPROCESSABLE_ENTITY);
        });

        $this->renderable(function (InvalidArgumentException $e, $request) {
            $response = [
                'description' => $e->getMessage(),
            ];
            return $this->asJson($response, Response::HTTP_BAD_REQUEST);
        });

        $this->renderable(function (Throwable $e, $request) {
            $message = app()->environment(['local', 'staging']) ? $e->getMessage() : self::INTERNAL_SERVER_ERROR_MESSAGE;
            return $this->asJson($message, Response::HTTP_INTERNAL_SERVER_ERROR);
        });
    }

    private function asJson($message, int $statusCode)
    {
        return response()->json($message, $statusCode);
    }
}
