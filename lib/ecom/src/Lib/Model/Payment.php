<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Ecom\Lib\Model;

use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Model\Payment\Application\CoApplicant;
use Resursbank\Ecom\Lib\Model\Payment\ApplicationResponse;
use Resursbank\Ecom\Lib\Model\Payment\Customer;
use Resursbank\Ecom\Lib\Model\Payment\Metadata;
use Resursbank\Ecom\Lib\Model\Payment\Order;
use Resursbank\Ecom\Lib\Model\Payment\Order\PossibleAction as PossibleActionModel;
use Resursbank\Ecom\Lib\Model\Payment\PaymentMethod;
use Resursbank\Ecom\Lib\Model\Payment\TaskRedirectionUrls;
use Resursbank\Ecom\Lib\Order\CountryCode;
use Resursbank\Ecom\Lib\Validation\StringValidation;
use Resursbank\Ecom\Module\Payment\Enum\PossibleAction;
use Resursbank\Ecom\Module\Payment\Enum\Status;

/**
 * Payment model used in the GET /payment call.
 */
class Payment extends Model
{
    /**
     * Payment data container that is also used by Search. When Search is active, some
     * returned fields are not guaranteed to be present; those fields are also nullable.
     * Application and countryCode is currently not showing in Search, so to make
     * Search compatible with the Payment model, we are temporary setting the missing fields
     * with empty defaults.
     *
     * @param array $paymentActions
     * @throws EmptyValueException
     * @throws IllegalValueException
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @todo Missing unit tests ECP-254
     */
    public function __construct(
        public readonly string $id,
        public readonly string $created,
        public readonly string $storeId,
        public readonly Customer $customer,
        public readonly Status $status,
        public readonly array $paymentActions = [],
        public readonly ?PaymentMethod $paymentMethod = null,
        public readonly ?CountryCode $countryCode = null,
        public readonly ?Order $order = null,
        public readonly ?ApplicationResponse $application = null,
        public readonly ?Metadata $metadata = null,
        public readonly ?CoApplicant $coApplicant = null,
        public readonly ?TaskRedirectionUrls $taskRedirectionUrls = null,
        private readonly StringValidation $stringValidation = new StringValidation()
    ) {
        $this->validateId();
        $this->validateCreated();
        $this->validateStoreId();
    }

    /**
     * Checks if payment can be cancelled.
     */
    public function canCancel(): bool
    {
        return $this->canPerformAction(actionType: PossibleAction::CANCEL);
    }

    /**
     * Checks if payment can be partially cancelled.
     */
    public function canPartiallyCancel(): bool
    {
        return $this->canPerformAction(
            actionType: PossibleAction::PARTIAL_CANCEL
        );
    }

    /**
     * Checks if payment can be captured.
     */
    public function canCapture(): bool
    {
        return $this->canPerformAction(actionType: PossibleAction::CAPTURE);
    }

    /**
     * Checks if payment can be partially captured.
     */
    public function canPartiallyCapture(): bool
    {
        return $this->canPerformAction(
            actionType: PossibleAction::PARTIAL_CAPTURE
        );
    }

    /**
     * Checks if payment can be refunded.
     */
    public function canRefund(): bool
    {
        return $this->canPerformAction(actionType: PossibleAction::REFUND);
    }

    /**
     * Checks if payment can be partially refunded.
     */
    public function canPartiallyRefund(): bool
    {
        return $this->canPerformAction(
            actionType: PossibleAction::PARTIAL_REFUND
        );
    }

    /**
     * Alias for canRefund.
     */
    public function canCredit(): bool
    {
        return $this->canRefund();
    }

    /**
     * Returns true if payment is frozen.
     */
    public function isFrozen(): bool
    {
        return $this->status === Status::FROZEN;
    }

    /**
     * Whether payment is processable.
     */
    public function isProcessable(): bool
    {
        return match ($this->status) {
            Status::ACCEPTED, Status::TASK_REDIRECTION_REQUIRED => true,
            default => false
        };
    }

    /**
     * Whether payment is captured.
     */
    public function isCaptured(): bool
    {
        return
            !$this->canCapture() &&
            !$this->canPartiallyCapture() &&
            $this->order->authorizedAmount === 0.0 &&
            $this->order->capturedAmount > 0.0 &&
            $this->order->capturedAmount !== $this->order->refundedAmount
        ;
    }

    /**
     * Whether payment is refunded.
     */
    public function isRefunded(): bool
    {
        return
            $this->order->authorizedAmount === 0.0 &&
            $this->order->capturedAmount > 0.0 &&
            $this->order->capturedAmount === $this->order->refundedAmount
        ;
    }

    /**
     * Whether payment is cancelled.
     */
    public function isCancelled(): bool
    {
        return
            $this->order->authorizedAmount === 0.0 &&
            $this->order->canceledAmount === $this->order->totalOrderAmount
        ;
    }

    /**
     * NOTE: We cannot test date format because Resurs Bank will return
     * inconsistent values for the same properties (sometimes ATOM compatible,
     * sometimes containing a up to 9 digit microsecond suffix).
     *
     * @throws IllegalValueException
     */
    private function validateCreated(): void
    {
        $this->stringValidation->isTimestampDate(value: $this->created);
    }

    /**
     * Validate that an (uu)id exists on the payment.
     *
     * @throws EmptyValueException
     * @throws IllegalValueException
     */
    private function validateId(): void
    {
        $this->validateUuid(uuid: $this->id);
    }

    /**
     * Validate existing store (uu)id.
     *
     * @throws EmptyValueException
     * @throws IllegalValueException
     */
    private function validateStoreId(): void
    {
        $this->validateUuid(uuid: $this->storeId);
    }

    /**
     * Validate that a string is an uuid and not empty.
     *
     * @throws EmptyValueException
     * @throws IllegalValueException
     */
    private function validateUuid(string $uuid): void
    {
        $this->stringValidation->notEmpty(value: $uuid);
        $this->stringValidation->isUuid(value: $uuid);
    }

    /**
     * Check if specified PossibleAction can be performed on this Payment
     */
    private function canPerformAction(PossibleAction $actionType): bool
    {
        if (!$this->order) {
            return false;
        }

        /** @var PossibleActionModel $action */
        foreach ($this->order->possibleActions as $action) {
            if ($action->action === $actionType) {
                return true;
            }
        }

        return false;
    }
}
