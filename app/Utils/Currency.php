<?php

namespace App\Utils;

use NumberFormatter;

class Currency
{
    public static function formatCurrency(float $amount): string
    {
        $formatter = new NumberFormatter('sq_AL', NumberFormatter::CURRENCY);

        return $formatter->formatCurrency($amount, 'ALL');
    }
}
