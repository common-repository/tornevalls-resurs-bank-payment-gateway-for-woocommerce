<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Woocommerce\Modules\Gateway;

use Resursbank\Ecom\Module\PaymentMethod\Repository as PaymentMethodRepository;
use Resursbank\Woocommerce\Database\Options\Advanced\StoreId;
use Resursbank\Woocommerce\Util\Log;
use Resursbank\Woocommerce\Util\Route;
use Throwable;

use function is_array;

/**
 * Implementation of Resurs Bank gateway.
 */
class Gateway
{
    /**
     * Executes in admin.
     */
    public static function initAdmin(): void
    {
        // Clicking on the "Manage" btn for our gateway redirects to settings.
        $page = $_GET['page'] ?? '';
        $tab = $_GET['tab'] ?? '';
        $section = $_GET['section'] ?? '';

        if (
            $page === 'wc-settings' &&
            $tab === 'checkout' &&
            $section === RESURSBANK_MODULE_PREFIX
        ) {
            Route::redirectToSettings();
        }

        // Register event listener to append our gateway.
        add_filter(
            'woocommerce_payment_gateways',
            'Resursbank\Woocommerce\Modules\Gateway\Gateway::addGateway'
        );
    }

    /**
     * Executes on frontend.
     *
     * @noinspection PhpArgumentWithoutNamedIdentifierInspection
     */
    public static function initFrontend(): void
    {
        add_filter(
            'woocommerce_available_payment_gateways',
            'Resursbank\Woocommerce\Modules\Gateway\Gateway::addPaymentMethods'
        );
        add_filter(
            'woocommerce_gateway_icon',
            'Resursbank\Woocommerce\Modules\Gateway\Gateway::modifyIcon',
            10,
            1
        );
    }

    /**
     * Add Resurs Bank payment gateway to list of payment methods in admin.
     */
    public static function addGateway(mixed $gateways): mixed
    {
        if (is_array(value: $gateways)) {
            $gateways[] = Resursbank::class;
        }

        return $gateways;
    }

    /**
     * Add Resurs Bank payment methods to list of available methods in checkout.
     */
    public static function addPaymentMethods(mixed $gateways): mixed
    {
        if (!is_array(value: $gateways)) {
            return $gateways;
        }

        try {
            $paymentMethodList = PaymentMethodRepository::getPaymentMethods(
                storeId: StoreId::getData()
            );

            foreach ($paymentMethodList as $paymentMethod) {
                $gateway = new Resursbank(method: $paymentMethod);

                if (!$gateway->is_available()) {
                    continue;
                }

                $gateways[$paymentMethod->id] = $gateway;
            }
        } catch (Throwable $e) {
            Log::error(error: $e);
        }

        return $gateways;
    }

    /**
     * Apply styling to payment method icons.
     */
    public static function modifyIcon(mixed $icon): string
    {
        if ($icon === '') {
            return $icon;
        }

        return preg_replace(
            pattern: '/>$/',
            replacement: ' style="padding:0;margin:0;max-height:1em;vertical-align:middle;">',
            subject: $icon
        );
    }
}
