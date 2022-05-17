<?php

declare(strict_types=1);

namespace IzzyPay\Validators;

use IzzyPay\Models\Urls;

class UrlsValidator
{
    /**
     * @param Urls $urls
     * @return array
     */
    public function validateUrls(Urls $urls): array
    {
        $errors = [];

        if (($urls->getIpn() === '') || !filter_var($urls->getIpn(), FILTER_VALIDATE_URL)) {
            $errors[] = 'ipn';
        }

        return $errors;
    }
}
