<?php

declare(strict_types=1);

namespace IzzyPay\Validators;

use IzzyPay\Exceptions\InvalidUrlsException;
use IzzyPay\Models\RedirectUrls;

class RedirectUrlsValidator
{
    /**
     * @param RedirectUrls $urls
     * @return void
     * @throws InvalidUrlsException
     */
    public function validateUrls(RedirectUrls $urls): void
    {
        $errors = [];

        if (!filter_var($urls->getAccepted(), FILTER_VALIDATE_URL)) {
            $errors[] = 'accepted';
        }

        if (!filter_var($urls->getRejected(), FILTER_VALIDATE_URL)) {
            $errors[] = 'rejected';
        }

        if (!filter_var($urls->getCancelled(), FILTER_VALIDATE_URL)) {
            $errors[] = 'cancelled';
        }

        if (!filter_var($urls->getIpn(), FILTER_VALIDATE_URL)) {
            $errors[] = 'ipn';
        }

        if (!filter_var($urls->getCheckoutUrl(), FILTER_VALIDATE_URL)) {
            $errors[] = 'checkoutUrl';
        }

        if (count($errors) > 0) {
            throw new InvalidUrlsException($errors);
        }
    }
}
