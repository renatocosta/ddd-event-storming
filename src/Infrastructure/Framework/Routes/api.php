<?php

use Domain\Model\User\UserAbilities;
use Illuminate\Support\Facades\Route;
use Interfaces\Incoming\WebApi\Controllers\AuthenticationController;
use Interfaces\Incoming\WebApi\Controllers\CodeResenderController;
use Interfaces\Incoming\WebApi\Controllers\OrderController;
use Interfaces\Incoming\WebApi\Controllers\ProjectReportsController;
use Interfaces\Incoming\WebApi\Controllers\UserController;
use Symfony\Component\HttpFoundation\Response;

Route::any('/', function () {
    return abort(Response::HTTP_NOT_FOUND);
});

Route::group(['prefix' => 'v1'], function () {

    Route::middleware(['throttle:global'])->group(function () {

        Route::group(['prefix' => 'order'], function () {
            Route::post('/', [OrderController::class, 'create']);
            Route::patch('/confirm/{orderId}/{sms_verification_code}', [OrderController::class, 'confirm']);
            Route::get('/{orderNumber}', [OrderController::class, 'fetchBy'])->middleware(['auth:sanctum', 'abilities:' . UserAbilities::MANAGE_ORDER]);
            Route::get('/', [OrderController::class, 'fetchAll'])->middleware(['auth:sanctum', 'abilities:' . UserAbilities::MANAGE_GLOBAL]);
            Route::post('/reviews', [OrderController::class, 'sendRating'])->middleware(['auth:sanctum', 'abilities:' . UserAbilities::MANAGE_REVIEW]);
            Route::post('/tip', [OrderController::class, 'sendTip'])->middleware(['auth:sanctum', 'abilities:' . UserAbilities::MANAGE_REVIEW]);
            Route::get('/check-address-availability/{address}/{unitNumber?}', [OrderController::class, 'checkAddressAvailability']);
            Route::patch('/cleaners', [OrderController::class, 'addMoreCleaners']);
        });

        Route::group(['prefix' => 'project'], function () {
            Route::get('/', [ProjectReportsController::class, 'fetchAll'])->middleware(['auth:sanctum', 'abilities:' . UserAbilities::MANAGE_GLOBAL]);
            Route::get('/follow-up/state/{orderNumber}', [ProjectReportsController::class, 'currentState'])->middleware(['auth:sanctum', 'abilities:' . UserAbilities::MANAGE_PROJECT]);
            Route::get('/follow-up/{order_number}', [ProjectReportsController::class, 'followUp'])->middleware(['auth:sanctum', 'abilities:' . UserAbilities::MANAGE_PROJECT]);
            Route::get('/follow-up/{orderNumber}/{status}', [ProjectReportsController::class, 'fetchBy'])->middleware(['auth:sanctum', 'abilities:' . UserAbilities::MANAGE_PROJECT]);
            Route::post('/share/{orderId}', [ProjectReportsController::class, 'share'])->middleware(['auth:sanctum', 'abilities:' . UserAbilities::SHARE_REPORT]);
            Route::get('/{orderId}/report', [ProjectReportsController::class, 'reported']);
        });

        Route::group(['prefix' => 'payment-method'], function () {
            Route::patch('update', [UserController::class, 'updatePaymentMethod'])->middleware(['auth:sanctum', 'abilities:' . UserAbilities::MANAGE_PAYMENT]);
        });

        Route::group(['prefix' => 'authentication'], function () {
            Route::post('byOrderNumber', [AuthenticationController::class, 'authByOrderNumber']);
            Route::post('/', [AuthenticationController::class, 'authByEmail']);
        });
    });

    Route::group(['prefix' => 'user'], function () {
        Route::middleware(['throttle:global'])->group(function () {
            Route::put('update', [UserController::class, 'update']);
            Route::get('/', [UserController::class, 'fetchAll'])->middleware(['auth:sanctum', 'abilities:' . UserAbilities::MANAGE_GLOBAL]);
        });

        Route::middleware(['throttle:sms-code-resender'])->group(function () {
            Route::post('/resend-code', [CodeResenderController::class, 'resend']);
        });
    });
});
