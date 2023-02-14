<?php

declare(strict_types=1);

namespace IzzyPay\Validators;

use IzzyPay\Exceptions\InvalidUrlsException;
use IzzyPay\Models\Urls;

class UrlsValidator
{
    /**
     * @param Urls $urls
     * @return void
     * @throws InvalidUrlsException
     */
    public function validateUrls(Urls $urls): void
    {
        $errors = [];

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
